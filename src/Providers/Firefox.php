<?php

namespace Notimatica\Driver\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Driver;
use Notimatica\Driver\Events\NotificationFailed;
use Notimatica\Driver\Events\NotificationSent;
use Notimatica\Driver\Support\ProviderWithHttpClient;

class Firefox extends ProviderWithHttpClient
{
    const NAME = 'firefox';

    /**
     * Split endpoints for batch requests.
     *
     * @param  array $endpoints
     * @return array
     */
    protected function batch(array $endpoints)
    {
        return array_chunk($endpoints, (int) $this->config['max_batch_endpoints']);
    }

    /**
     * Send notification.
     *
     * @param  Notification $notification
     * @param  Subscriber[] $subscribers
     */
    public function send(Notification $notification, array $subscribers)
    {
        $this->flush(
            $subscribers,
            function (Response $response) use ($notification) {
                try {
                    if ($response->getStatusCode() != 201) {
                        $response = json_decode($response->getBody());

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new \Exception('Firefox: bad json');
                        }

                        throw new \Exception($response->message);
                    }

                    static::$dispatcher->emit(new NotificationSent($notification));
                } catch (\Exception $e) {
                    static::$dispatcher->emit(new NotificationFailed($notification));
                }
            },
            function () use ($notification) {
                static::$dispatcher->emit(new NotificationFailed($notification));
            }
        );
    }

    /**
     * Send request.
     *
     * @param  array $subscribers
     * @param  array|null $payload
     * @return \Generator
     */
    protected function prepareRequests($subscribers, $payload = null)
    {
        $headers = $this->headers;
        $content = '';

        foreach ($subscribers as $subscriber) {
            $headers['Content-Length'] = strlen($content);

            yield new Request('POST', $this->getUrl($subscriber), $headers, $content);
        }
    }

    /**
     * Generate endpoint url.
     *
     * @param  Subscriber $subscriber
     * @return string
     */
    protected function getUrl(Subscriber $subscriber)
    {
        return $this->config['service_url'] . '/' . $subscriber->getToken();
    }

    /**
     * If provider is enabled.
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return true;
    }
}
