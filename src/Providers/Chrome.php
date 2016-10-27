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
use ZipStream\ZipStream;

class Chrome extends AbstractProvider
{
    const NAME            = 'chrome';
    const DEFAULT_TTL     = 2419200;
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
        static::$headers['Authorization'] = 'key=' . $config['api_key'];
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
        static::initBrowser($this->config);
        $count = count($subscribers);

        $this->flush(
            $subscribers,
            function (Response $response, $index) use ($notification, $count) {
                try {
                    $response = json_decode($response->getBody());

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception();
                    }

                    if ($response->success > 0) {
                        Driver::emitEvent(new NotificationSent($notification, (int) $response->success));
                    }

                    if ($response->failure > 0) {
                        Driver::emitEvent(new NotificationFailed($notification, (int) $response->failure));
                    }
                } catch (\Exception $e) {
                    Driver::emitEvent(new NotificationFailed($notification, $this->calculateChunkSize($count, $index)));
                }
            },
            function ($reason, $index) use ($notification, $count) {
                Driver::emitEvent(new NotificationFailed($notification, $this->calculateChunkSize($count, $index)));
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
        $url = $this->config['url'];
        $headers = static::$headers;

        foreach ($this->batch($subscribers) as $index => $chunk) {
            $content = $this->getContent($chunk);
            $headers['Content-Length'] = strlen($content);

            yield new Request('POST', $url, $headers, $content);
        }
    }

    /**
     * Calculate chunk size. FUUUUUU!!!11
     *
     * @param  int $count
     * @param  int $index
     * @return int
     */
    protected function calculateChunkSize($count, $index)
    {
        $chunk = (int) $this->config['batch_chunk_size'];

        $index++;

        $multiply = $index * $chunk;
        $chunks = ceil($count / $chunk);

        if ($multiply <= $count) {
            $return = $chunk;
        } elseif ($multiply > $count + $chunk) {
            $return = 0;
        } else {
            $return = $count - ($chunks - 1) * $chunk;
        }

        return (int) $return;
    }

    /**
     * Send notification to endpoints.
     *
     * @param  Subscriber[] $subscribers
     * @return array
     */
    public function getContent(array $subscribers)
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
     * Distribute connection package.
     *
     * @param array $extra
     * @throws \ZipStream\Exception\FileNotFoundException
     * @throws \ZipStream\Exception\FileNotReadableException
     */
    public function connectionPackage($extra = [])
    {
        $zip = new ZipStream('notimatica.zip');
        $zip->addFileFromPath('notimatica-sw.js', public_path('notimatica-sw.js'));
        $zip->addFile('manifest.json', $this->manifest());
        $zip->finish();
    }

    /**
     * Generate manifest file text.
     *
     * @return string
     */
    protected function manifest()
    {
        return json_encode([
            'name' => $this->project->name,
            'gcm_sender_id' => $this->config['sender_id'],
        ], JSON_PRETTY_PRINT);
    }
}
