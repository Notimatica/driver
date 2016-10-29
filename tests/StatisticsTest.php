<?php

namespace Notimatica\Driver\Tests;

use League\Event\ListenerProviderInterface;
use Notimatica\Driver\Contracts\NotificationRepository;
use Notimatica\Driver\Events\NotificationClicked;
use Notimatica\Driver\Events\NotificationDelivered;
use Notimatica\Driver\Events\NotificationFailed;
use Notimatica\Driver\Events\NotificationSent;
use Notimatica\Driver\Statistics;

class StatisticsTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_created()
    {
        $statistics = new Statistics($this->makeNotificationRepository());

        $this->assertInstanceOf(Statistics::class, $statistics);
        $this->assertInstanceOf(ListenerProviderInterface::class, $statistics);
    }

    /**
     * @test
     */
    public function it_can_increment_sent()
    {
        $notification = $this->makeNotification();
        $repository = \Mockery::mock(NotificationRepository::class);
        $repository->shouldReceive('increment')->with($notification, 'sent', 1)->once();

        $statistics = new Statistics($repository);

        $statistics->sent(new NotificationSent($notification));
    }

    /**
     * @test
     */
    public function it_can_increment_delivered()
    {
        $notification = $this->makeNotification();
        $repository = \Mockery::mock(NotificationRepository::class);
        $repository->shouldReceive('increment')->with($notification, 'delivered', 1)->once();

        $statistics = new Statistics($repository);

        $statistics->delivered(new NotificationDelivered($notification));
    }

    /**
     * @test
     */
    public function it_can_increment_clicked()
    {
        $notification = $this->makeNotification();
        $repository = \Mockery::mock(NotificationRepository::class);
        $repository->shouldReceive('increment')->with($notification, 'clicked', 1)->once();

        $statistics = new Statistics($repository);

        $statistics->clicked(new NotificationClicked($notification));
    }

    /**
     * @test
     */
    public function it_can_increment_failed()
    {
        $notification = $this->makeNotification();
        $repository = \Mockery::mock(NotificationRepository::class);
        $repository->shouldReceive('increment')->with($notification, 'failed', 1)->once();

        $statistics = new Statistics($repository);

        $statistics->failed(new NotificationFailed($notification));
    }
}