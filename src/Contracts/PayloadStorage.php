<?php

namespace Notimatica\Driver\Contracts;

interface PayloadStorage
{
    /**
     * Search and return payload for subscriber.
     *
     * @param  Subscriber $subscriber
     * @return Notification
     */
    public function getNotification(Subscriber $subscriber);

    /**
     * Save payload for endpoint.
     *
     * @param  Notification $notification
     * @param  Subscriber $subscriber
     * @param  int $lifetime
     */
    public function assignNotificationToSubscriber(Notification $notification, Subscriber $subscriber, $lifetime = 86400);
}
