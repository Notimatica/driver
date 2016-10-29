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
     * @param  Subscriber $subscriber
     * @param  Notification $notification
     * @param  int $lifetime
     */
    public function assignNotificationToSubscriber(Subscriber $subscriber, Notification $notification, $lifetime = 86400);
}
