<?php

namespace Notimatica\Driver\Tests;

class ReceiveTest extends TestCase
{
    /** @test */
    public function it_can_return_payload_on_deliver()
    {
        $driver = $this->makeDriver();

        $subscriber = $this->makeChromeSubscriber();

        $notification = $this->makeNotification();
        $notification->shouldReceive('wasDelivered')->once()->with(1);

        $payloadStorage = $driver->getPayloadStorage();
        $payload = $payloadStorage->makePayloadFromNotification($notification);

        $payloadStorage->shouldReceive('getPayloadForSubscriber')->once()->with($subscriber)->andReturn($payload);

        $notificationsRepository = $driver->getNotificationRepository();
        $notificationsRepository->shouldReceive('find')->with($notification->getId())->andReturn($notification);

        $this->assertSame($payload, $driver->retrievePayload($subscriber));
    }

    /** @test */
    public function it_can_handle_click_and_url_response()
    {
        $notification = $this->makeNotification();
        $notification->shouldReceive('wasClicked')->once()->with(1);

        $driver = $this->makeDriver();

        $this->assertSame($notification->getUrl(), $driver->processClicked($notification));
    }
}