<?php

namespace Notimatica\Driver\Providers;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Project;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Events\NotificationFailed;
use Notimatica\Driver\Events\NotificationSent;
use Notimatica\Driver\Support\ProviderWithHttpClient;

class Chrome extends ProviderWithHttpClient
{
    const NAME = 'chrome';

    /**
     * @var Project
     */
    protected $project;

    /**
     * Chrome constructor.
     *
     * @param Project $project
     * @param ClientInterface $client
     */
    public function __construct(Project $project, ClientInterface $client)
    {
        parent::__construct($project, $client);

        $this->headers['Authorization'] = 'key=' . $this->config['api_key'];
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
                    $body = json_decode($response->getBody());

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception();
                    }

                    if ($body->success > 0) {
                        static::$dispatcher->emit(new NotificationSent($notification, (int) $body->success));
                    }

                    if ($body->failure > 0) {
                        static::$dispatcher->emit(new NotificationFailed($notification, (int) $body->failure));
                    }
                } catch (\Exception $e) {
                    static::$dispatcher->emit(new NotificationFailed($notification, $this->calculateChunkSize($index, $total)));
                }
            },
            function ($reason, $index) use ($notification, $total) {
                static::$dispatcher->emit(new NotificationFailed($notification, $this->calculateChunkSize($index, $total)));
            }
        );
    }

    /**
     * Split endpoints for batch requests.
     *
     * @param  Subscriber[] $endpoints
     * @param  int $chunkSize
     * @return array
     */
    protected function batch(array $endpoints, $chunkSize)
    {
        return array_chunk($endpoints, (int) $chunkSize);
    }

    /**
     * Send request.
     *
     * @param  Subscriber[] $subscribers
     * @param  null $payload
     * @return \Generator
     */
    protected function prepareRequests($subscribers, $payload = null)
    {
        $url = $this->config['service_url'];
        $chunkSize = $this->config['batch_chunk_size'];

        $headers = $this->headers;

        foreach ($this->batch($subscribers, $chunkSize) as $index => $chunk) {
            $content = $this->getRequestContent($chunk);
            $headers['Content-Length'] = strlen($content);

            yield new Request('POST', $url, $headers, $content);
        }
    }

    /**
     * Calculate chunk size.
     * Problem is, we don't know the latter chunk size and we need to calculate each chunk.
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
     * @return bool
     */
    public function isEnabled()
    {
        return ! empty($this->config['sender_id']) && ! empty($this->config['api_key']);
    }
}
