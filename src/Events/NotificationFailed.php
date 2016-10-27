<?php

namespace Notimatica\Driver\Events;

use League\Event\AbstractEvent;
use Notimatica\Driver\Contracts\Notification;

class NotificationFailed extends AbstractEvent
{
    /**
     * @var Notification
     */
    public $notification;
    /**
     * @var int
     */
    public $number;

    /**
     * Create a new NotificationSent.
     *
     * @param Notification $notification
     * @param int $number
     */
    public function __construct(Notification $notification, $number = 1)
    {
        $this->notification = $notification;
        $this->number = $number;
    }
}
