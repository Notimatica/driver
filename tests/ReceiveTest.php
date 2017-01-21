<?php

namespace Notimatica\Driver\Tests;

class ReceiveTest extends TestCase
{
    /** @test */
    public function it_can_return_payload_on_deliver()
    {
        $notification = $this->makeNotification();
        $notification->shouldReceive('wasDelivered')->once()->with(1);
        $payload = [
            'id' => $notification->getId(),
            'title' => $notification->getTitle(),
            'body' => $notification->getBody(),
        ];

        $subscriber = $this->makeChromeSubscriber();

        $driver = $this->makeDriver();

        $payloadStorage = $driver->getPayloadStorage();
        $payloadStorage->shouldReceive('getPayloadForSubscriber')->once()->with($subscriber)->andReturn($payload);

        $notificationsRepository = $driver->getNotificationRepository();
        $notificationsRepository->shouldReceive('find')->with($notification->getId())->andReturn($notification);

        $this->assertSame($payload, $driver->retrievePayload($subscriber));
    }
}