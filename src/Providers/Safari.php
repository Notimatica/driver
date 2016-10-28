<?php

namespace Notimatica\Driver\Providers;

use League\Flysystem\Filesystem;
use Notimatica\Driver\Apns\Certificate;
use Notimatica\Driver\Apns\Package;
use Notimatica\Driver\Apns\Payload;
use Notimatica\Driver\Apns\Streamer;
use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Driver;
use Notimatica\Driver\Events\NotificationFailed;
use Notimatica\Driver\Events\NotificationSent;
use ZipStream\ZipStream;

class Safari extends AbstractProvider
{
    const NAME = 'safari';

    /**
     * @var Filesystem
     */
    protected $storage;

    /**
     * Set files storage.
     *
     * @param  Filesystem $filesStorage
     * @return $this
     */
    public function setStorage(Filesystem $filesStorage)
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
        $certificate = new Certificate($this->project, $this->storage);
        $stream  = new Streamer($certificate, $this->config['url']);
        $payload = new Payload($notification);
        $payload = json_encode($payload);

        foreach ($this->prepareRequests($subscribers, $payload) as $message) {
            try {
                $stream->write($message);
                Driver::emitEvent(new NotificationSent($notification));
            } catch (\Exception $e) {
                Driver::emitEvent(new NotificationFailed($notification));
            }
        }

        $stream->close();
    }

    /**
     * Send request.
     *
     * @param  array $subscribers
     * @param  mixed $payload
     * @return \Generator
     */
    protected function prepareRequests($subscribers, $payload = null)
    {
        foreach ($subscribers as $subscriber) {
            yield chr(0) . chr(0) . chr(32) . pack('H*', $subscriber->token) . chr(0) . chr(strlen($payload)) . $payload;
        }
    }

    /**
     * Distribute connection package.
     *
     * @param  array $extra
     * @return ZipStream
     */
    public function connectionPackage($extra = [])
    {
        $certificate = new Certificate($this->project, $this->storage);
        $website = [
            'websiteName' => $this->project->name,
            'websitePushID' => $this->config['website_push_id'],
            'allowedDomains' => [$this->project->baseUrl],
            'urlFormatString' => "{$this->project->baseUrl}/go/%@",
            'webServiceURL' => $this->project->baseUrl . '/' . $this->config['subscribe_url'],
        ];

        array_merge($website, $extra);

        $package = new Package(
            $website,
            $this->project,
            $certificate,
            $this->storage
        );

        return $package->generate();
    }
}
