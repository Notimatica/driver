<?php

namespace Notimatica\Driver;

use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerProviderInterface;
use Notimatica\Driver\Contracts\NotificationRepository;
use Notimatica\Driver\Events\NotificationClicked;
use Notimatica\Driver\Events\NotificationDelivered;
use Notimatica\Driver\Events\NotificationFailed;
use Notimatica\Driver\Events\NotificationSent;

class Statistics implements ListenerProviderInterface
{
    /**
     * @var NotificationRepository
     */
    protected $notificationRepository;

    /**
     * Create a new StatisticsStorage.
     *
     * @param NotificationRepository $notificationRepository
     */
    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Number of sent pushes.
     *
     * @param  NotificationSent $event
     */
    public function sent(NotificationSent $event)
    {
        $this->notificationRepository->increment($event->notification, 'sent', $event->number);
    }

    /**
     * Number of delivered pushes.
     *
     * @param  NotificationDelivered $event
     */
    public function delivered(NotificationDelivered $event)
    {
        $this->notificationRepository->increment($event->notification, 'delivered', $event->number);
    }

    /**
     * Number of clicked pushes.
     *
     * @param  NotificationClicked $event
     */
    public function clicked(NotificationClicked $event)
    {
        $this->notificationRepository->increment($event->notification, 'clicked', $event->number);
    }

    /**
     * Number of failed pushes.
     *
     * @param  NotificationFailed $event
     */
    public function failed(NotificationFailed $event)
    {
        $this->notificationRepository->increment($event->notification, 'failed', $event->number);
    }

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
