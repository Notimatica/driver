<?php

namespace Notimatica\Driver\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Driver;
use Notimatica\Driver\Events\NotificationFailed;
use Notimatica\Driver\Events\NotificationSent;

class Firefox extends AbstractProvider
{
    const NAME = 'firefox';
    const DEFAULT_TTL = 2419200;
    const DEFAULT_TIMEOUT = 30;

    /**
     * @var array
     */
    protected static $headers = [
        'Content-Type' => 'application/json',
    ];

    /**
     * Init Browser.
     *
     * @param array $config
     */
    protected static function initBrowser(array $config)
    {
        if (static::$browser) {
            return;
        }

        static::$browser = new Client([
            'timeout' => isset($config['timeout']) ? $config['timeout'] : static::DEFAULT_TIMEOUT,
        ]);

        static::$headers['TTL'] = isset($config['ttl']) ? $config['ttl'] : static::DEFAULT_TTL;
    }

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
        static::initBrowser($this->config);

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

                    Driver::emit(new NotificationSent($notification));
                } catch (\Exception $e) {
                    Driver::emit(new NotificationFailed($notification));
                }
            },
            function () use ($notification) {
                Driver::emit(new NotificationFailed($notification));
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
        $headers = static::$headers;
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
        return $this->config['url'] . '/' . $subscriber->getToken();
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
