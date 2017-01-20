<?php

namespace Notimatica\Driver\Providers;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Driver;
use Notimatica\Driver\Events\NotificationFailed;
use Notimatica\Driver\Events\NotificationSent;
use Notimatica\Driver\Support\ProviderWithHttpClient;

class Chrome extends ProviderWithHttpClient
{
    const NAME = 'chrome';

    /**
     * Chrome constructor.
     *
     * @param array $config
     * @param ClientInterface $client
     */
    public function __construct(array $config, ClientInterface $client)
    {
        parent::__construct($config, $client);

        $this->headers['Authorization'] = 'key=' . $config['api_key'];
    }

    /**
     * Split endpoints for batch requests.
     *
     * @param  array $endpoints
     * @return array
     */
    protected function batch(array $endpoints)
    {
        return array_chunk($endpoints, (int) $this->config['batch_chunk_size']);
    }

    /**
     * Send notification.
     *
     * @param  Notification $notification
     * @param  Subscriber[] $subscribers
     */
    public function send(Notification $notification, array $subscribers)
    {
        $total = count($subscribers);

        $this->flush(
            $subscribers,
            function (Response $response, $index) use ($notification, $total) {
                try {
                    $response = json_decode($response->getBody());

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception();
                    }

                    if ($response->success > 0) {
                        Driver::emit(new NotificationSent($notification, (int) $response->success));
                    }

                    if ($response->failure > 0) {
                        Driver::emit(new NotificationFailed($notification, (int) $response->failure));
                    }
                } catch (\Exception $e) {
                    Driver::emit(new NotificationFailed($notification, $this->calculateChunkSize($index, $total)));
                }
            },
            function ($reason, $index) use ($notification, $total) {
                Driver::emit(new NotificationFailed($notification, $this->calculateChunkSize($index, $total)));
            }
        );
    }

    /**
     * Send request.
     *
     * @param  array $subscribers
     * @param  null $payload
     * @return \Generator
     */
    protected function prepareRequests($subscribers, $payload = null)
    {
        $url = $this->config['service_url'];
        $headers = $this->headers;

        foreach ($this->batch($subscribers) as $index => $chunk) {
            $content = $this->getRequestContent($chunk);
            $headers['Content-Length'] = strlen($content);

            yield new Request('POST', $url, $headers, $content);
        }
    }

    /**
     * Calculate chunk size.
     * Problem is, we don't know the latter chunk size.
     *
     * @param  int $index
     * @param  int $total
     * @return int
     */
    protected function calculateChunkSize($index, $total)
    {
        $chunkSize = (int) $this->config['batch_chunk_size'];

        $offset = ($index + 1) * $chunkSize;
        $chunks = ceil($total / $chunkSize);

        if ($offset <= $total) {
            $return = $chunkSize;
        } elseif ($offset > $total + $chunkSize) {
            $return = 0;
        } else {
            $return = $total - ($chunks - 1) * $chunkSize;
        }

        return (int) $return;
    }

    /**
     * Send notification to endpoints.
     *
     * @param  Subscriber[] $subscribers
     * @return array
     */
    protected function getRequestContent(array $subscribers)
    {
        $tokens = [];

        /** @var Subscriber $subscriber */
        foreach ($subscribers as $subscriber) {
            $tokens[] = $subscriber->getToken();
        }

        return json_encode([
            'registration_ids' => $tokens,
        ]);
    }

    /**
     * Generate manifest file text.
     *
     * @return string
     */
    public function manifest()
    {
        return json_encode([
            'name' => $this->project->getName(),
            'gcm_sender_id' => $this->config['sender_id'],
        ], JSON_PRETTY_PRINT);
    }

    /**
     * If provider is enabled.
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return ! empty($this->config['sender_id']) && ! empty($this->config['api_key']);
    }
}
