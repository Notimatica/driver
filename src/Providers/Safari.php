<?php

namespace Notimatica\Driver\Providers;

use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Notimatica\Driver\Apns\Certificate;
use Notimatica\Driver\Apns\Package;
use Notimatica\Driver\Apns\Payload;
use Notimatica\Driver\Apns\Streamer;
use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Driver;
use Notimatica\Driver\Events\NotificationFailed;
use Notimatica\Driver\Events\NotificationSent;
use Ramsey\Uuid\Uuid;
use ZipStream\ZipStream;

class Safari extends AbstractProvider
{
    const NAME = 'safari';

    /**
     * @var Filesystem
     */
    protected $localStorage;
    /**
     * @var Filesystem
     */
    protected $publicStorage;

    /**
     * Set storage.
     *
     * @param  Factory $storage
     * @return $this
     */
    public function setStorage(Factory $storage)
    {
        $this->localStorage = $storage->disk($this->config['local_storage']);
        $this->publicStorage = $storage->disk($this->config['public_storage']);

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
        $certificate = new Certificate($this->project, $this->localStorage);
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
        $certificate = new Certificate($this->project, $this->localStorage);
        $website = [
            'websiteName' => $this->project->name,
            'websitePushID' => $this->provider->config['website_push_id'],
            'allowedDomains' => [$this->project->base_url, "https://{$this->project->subdomain}.notimatica.io", 'https://dev.notimatica.io'],
            'urlFormatString' => "https://{$this->project->subdomain}.notimatica.io/go/%@",
            'authenticationToken' => Uuid::uuid4(),
            'webServiceURL' => sprintf('https://api.notimatica.io/v1/projects/%s/safari', $this->project->uuid),
        ];

        $package = new Package(
            $website,
            $this->project,
            $certificate,
            $this->publicStorage
        );

        return $package->generate();
    }
}
