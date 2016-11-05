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

class Safari extends AbstractProvider
{
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
        $certificate = new Certificate($this->config['assets']['certificates'], $this->storage);
        $stream  = new Streamer($certificate, $this->config['service_url']);
        $payload = new Payload($notification);
        $payload = json_encode($payload);

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
        $certificate = new Certificate($this->config['assets']['certificates'], $this->storage);
        $website = [
            'websiteName' => $this->project->getName(),
            'websitePushID' => $this->config['website_push_id'],
            'allowedDomains' => [$this->project->getBaseUrl()],
            'urlFormatString' => "{$this->project->getBaseUrl()}/go/%@",
            'webServiceURL' => $this->project->getBaseUrl() . '/' . $this->config['subscribe_url'],
        ];

        array_merge($website, $extra);

        $package = new Package(
            $this->config['assets']['package'],
            $this->config['assets']['icons'],
            $website, $certificate, $this->storage
        );

        // Hack to not to rebuild package each time.
        $path = $package->getPackagePath();
        if ($this->storage->has($path)) {
            return $path;
        }

        return $package->generate();
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
