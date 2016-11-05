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
     * Set files storage.
     *
     * @param  FilesystemInterface $filesStorage
     * @return $this
     */
    public function setStorage(FilesystemInterface $filesStorage)
    {
        $this->storage = $filesStorage;

        return $this;
    }

    /**
     * Send notification.
     *
     * @param  Notification $notification
     * @param  Subscriber[] $subscribers
     */
    public function send(Notification $notification, array $subscribers)
    {
        $stream  = new Streamer($this->makeCertificate(), $this->config['service_url']);
        $payload = json_encode(new Payload($notification));

        foreach ($this->prepareRequests($subscribers, $payload) as $message) {
            try {
                $stream->write($message);
                Driver::emit(new NotificationSent($notification));
            } catch (\Exception $e) {
                Driver::emit(new NotificationFailed($notification));
            }
        }

        $stream->close();
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
     * Makes certificate object.
     *
     * @return Certificate
     */
    public function makeCertificate()
    {
        return new Certificate($this->config['assets']['certificates'], $this->storage);
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
            $website, $this->makeCertificate(), $this->storage
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
     * Return storage.
     *
     * @return FilesystemInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
