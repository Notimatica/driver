<?php

namespace Notimatica\Driver\Providers;

use League\Flysystem\FilesystemInterface;
use Notimatica\Driver\Apns\Certificate;
use Notimatica\Driver\Apns\Package;
use Notimatica\Driver\Apns\Payload;
use Notimatica\Driver\Apns\Streamer;
use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Driver;
use Notimatica\Driver\Events\NotificationFailed;
use Notimatica\Driver\Events\NotificationSent;
use Notimatica\Driver\Support\MakesUrls;

class Safari extends AbstractProvider
{
    use MakesUrls;

    const NAME = 'safari';

    /**
     * @var FilesystemInterface
     */
    protected $storage;
    /**
     * @var Streamer
     */
    protected $streamer;

    public function __construct(array $config, FilesystemInterface $filesStorage, Streamer $streamer)
    {
        parent::__construct($config);

        $this->storage = $filesStorage;
        $this->streamer = $streamer;
    }

    /**
     * Send notification.
     *
     * @param  Notification $notification
     * @param  Subscriber[] $subscribers
     */
    public function send(Notification $notification, array $subscribers)
    {
        $payload = json_encode(new Payload($notification));

        foreach ($this->prepareRequests($subscribers, $payload) as $message) {
            try {
                $this->streamer->write($message);
                Driver::emit(new NotificationSent($notification));
            } catch (\Exception $e) {
                Driver::emit(new NotificationFailed($notification));
            }
        }

        $this->streamer->close();
    }

    /**
     * Send request.
     *
     * @param  Subscriber[] $subscribers
     * @param  mixed $payload
     * @return \Generator
     */
    protected function prepareRequests($subscribers, $payload = null)
    {
        foreach ($subscribers as $subscriber) {
            try {
                yield chr(0) . chr(0) . chr(32) . pack('H*', $subscriber->getToken()) . chr(0) . chr(strlen($payload)) . $payload;
            } catch (\Exception $e) {
                // Skip catch, because we don't need to handle if subscriber has an invalid token.
            }
        }
    }

    /**
     * Distribute connection package.
     *
     * @param  array $extra
     * @return string|null
     */
    public function connectionPackage($extra = [])
    {
        // Hack to not to rebuild package each time.
        $path = $this->storage->getAdapter()->applyPathPrefix($this->config['assets']['package']);
        if ($this->storage->has($path)) {
            return $path;
        }

        $website = array_merge([
            'websiteName' => $this->project->getName(),
            'websitePushID' => $this->config['website_push_id'],
            'allowedDomains' => [$this->project->getBaseUrl()],
            'urlFormatString' => $this->makeClickUrl() . '/%@',
            'webServiceURL' => $this->formatUrlFromConfig($this->config['subscribe_url']),
        ], $extra);

        return $this->makePackage($website)->generate();
    }

    /**
     * Make package object.
     *
     * @param  array $website
     * @return Package
     */
    public function makePackage(array $website)
    {
        return new Package(
            $this->config['assets']['package'],
            $this->config['assets']['icons'],
            $website, $this->streamer->getCertificate(), $this->storage
        );
    }

    /**
     * If provider is enabled.
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return ! empty($this->config['website_push_id']);
    }

    /**
     * File storage getter.
     *
     * @return FilesystemInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * File storage setter.
     *
     * @param  FilesystemInterface $filesStorage
     */
    public function setStorage(FilesystemInterface $filesStorage)
    {
        $this->storage = $filesStorage;
    }

    /**
     * Streamer getter.
     *
     * @return Streamer
     */
    public function getStreamer()
    {
        return $this->streamer;
    }

    /**
     * Streamer setter.
     *
     * @param Streamer $streamer
     */
    public function setStreamer($streamer)
    {
        $this->streamer = $streamer;
    }
}
