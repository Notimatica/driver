<?php

namespace Notimatica\Driver;

use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\PayloadStorage as PayloadStorageContract;
use Notimatica\Driver\Contracts\Subscriber;

class PayloadStorage
{
    /**
     * @var PayloadStorageContract
     */
    protected $storage;
    /**
     * @var string
     */
    protected $keyPrefix = 'notification';
    /**
     * @var array
     */
    protected $config;

    /**
     * Create a new Redis storage.
     *
     * @param PayloadStorageContract $storage
     * @param array $config
     */
    public function __construct(PayloadStorageContract $storage, array $config)
    {
        $this->storage = $storage;
        $this->config = $config;
    }

    /**
     * Get payload for endpoint.
     *
     * @param  Subscriber|string $subscriber
     * @return Notification
     */
    public function getNotification($subscriber)
    {
        $uuid = $subscriber instanceof Subscriber ? $subscriber->getId() : $subscriber;

        return $this->storage->getNotification($uuid);
    }

    /**
     * Save payload for endpoint.
     *
     * @param  Subscriber $subscriber
     * @param  Notification $notification
     */
    public function assignNotificationToSubscriber(Subscriber $subscriber, Notification $notification)
    {
        $this->storage->assignNotificationToSubscriber(
            $subscriber->getId(),
            $notification->getId(),
            $this->config['subscriber_lifetime']
        );
    }
}
