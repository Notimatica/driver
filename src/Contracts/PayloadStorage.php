<?php namespace Notimatica\Driver\Contracts;

interface PayloadStorage
{
    /**
     * Search and return payload for subscriber.
     *
     * @param  string $subscriber
     * @return Notification
     */
    public function getNotification($subscriber);

    /**
     * Assign payload to subscriber.
     *
     * @param string $subscriber
     * @param string $notification
     * @param integer $lifetime
     */
    public function assignNotificationToSubscriber($subscriber, $notification, $lifetime);
}