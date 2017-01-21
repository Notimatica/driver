<?php

namespace Notimatica\Driver;

use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Project;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Support\MakesUrls;

abstract class PayloadStorage
{
    use MakesUrls;

    /**
     * Search and return payload for subscriber.
     *
     * @param  Subscriber $subscriber
     * @return Notification
     * @throws \RuntimeException
     */
    abstract public function getPayloadForSubscriber(Subscriber $subscriber);

    /**
     * Save payload for endpoint.
     *
     * @param  Notification $notification
     * @param  Subscriber $subscriber
     * @param  int $lifetime
     */
    abstract public function assignPayloadToSubscriber(
        Notification $notification,
        Subscriber $subscriber,
        $lifetime = 86400
    );

    /**
     * @param  Notification $notification
     * @return array
     */
    public function makePayloadFromNotification(Notification $notification)
    {
        return [
            'id' => $notification->getId(),
            'title' => $notification->getTitle(),
            'body' => $notification->getBody(),
            'icon' => $this->makeIconUrl($notification),
            'tag' => $this->makeTag($notification),
        ];
    }

    /**
     * Make notification tag.
     *
     * @return string
     */
    protected function makeTag($notification)
    {
        return hash('md5', $this->project->getBaseUrl());
    }
}
