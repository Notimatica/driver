<?php

namespace Notimatica\Driver\StatisticsStorages;

use Notimatica\Driver\Events\NotificationClicked;
use Notimatica\Driver\Events\NotificationDelivered;
use Notimatica\Driver\Events\NotificationFailed;
use Notimatica\Driver\Events\NotificationSent;

class Model extends AbstractStorage
{
    const NAME = 'model';

    /**
     * Number of sent pushes.
     *
     * @param  NotificationSent $event
     */
    public function sent(NotificationSent $event)
    {
        $event->notification->increment('sent', $event->number);
    }

    /**
     * Number of delivered pushes.
     *
     * @param  NotificationDelivered $event
     */
    public function delivered(NotificationDelivered $event)
    {
        $event->notification->increment('delivered', $event->number);
    }

    /**
     * Number of clicked pushes.
     *
     * @param  NotificationClicked $event
     */
    public function clicked(NotificationClicked $event)
    {
        $event->notification->increment('clicked', $event->number);
    }

    /**
     * Number of failed pushes.
     *
     * @param  NotificationFailed $event
     */
    public function failed(NotificationFailed $event)
    {
        $event->notification->increment('failed', $event->number);
    }
}
