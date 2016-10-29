<?php

namespace Notimatica\Driver\StatisticsStorages;

use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerProviderInterface;
use Notimatica\Driver\Events\NotificationClicked;
use Notimatica\Driver\Events\NotificationDelivered;
use Notimatica\Driver\Events\NotificationFailed;
use Notimatica\Driver\Events\NotificationSent;

abstract class AbstractStorage implements ListenerProviderInterface
{
    /**
     * Number of sent pushes.
     *
     * @param  NotificationSent $event
     */
    abstract public function sent(NotificationSent $event);

    /**
     * Number of delivered pushes.
     *
     * @param  NotificationDelivered $event
     */
    abstract public function delivered(NotificationDelivered $event);

    /**
     * Number of clicked pushes.
     *
     * @param  NotificationClicked $event
     */
    abstract public function clicked(NotificationClicked $event);

    /**
     * Number of failed pushes.
     *
     * @param  NotificationFailed $event
     */
    abstract public function failed(NotificationFailed $event);

    /**
     * Provide event.
     *
     * @param  ListenerAcceptorInterface $listener
     * @return $this
     */
    public function provideListeners(ListenerAcceptorInterface $listener)
    {
        $listener->addListener(NotificationSent::class, function ($e) {
            $this->sent($e);
        });
        $listener->addListener(NotificationDelivered::class, function ($e) {
            $this->delivered($e);
        });
        $listener->addListener(NotificationClicked::class, function ($e) {
            $this->clicked($e);
        });
        $listener->addListener(NotificationFailed::class, function ($e) {
            $this->failed($e);
        });

        return $this;
    }
}
