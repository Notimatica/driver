<?php

namespace Notimatica\Driver\Tests;

class SendingTest extends TestCase
{
    /**
     * @test
     */
    public function test_notification_send_to_chrome()
    {
        $driver = $this->makeDriver();
        $notification = $this->makeNotification();
        $notification->shouldReceive('increment')->with('fail', 1)->times(3);

        $driver->send($notification)->to([
            $this->makeChromeSubscriber(),
        ])->flush();
    }

    /**
     * @test
     */
    public function test_notification_send_to_firefox()
    {
        $driver = $this->makeDriver();
        $notification = $this->makeNotification();
        $notification->shouldReceive('increment')->with('fail', 1)->times(1);

        $driver->send($notification)->to([
            $this->makeFirefoxSubscriber(),
        ])->flush();
    }

    /**
     * @test
     */
    public function test_notification_send_to_Safari()
    {
        $driver = $this->makeDriver();
        $notification = $this->makeNotification();
        $notification->shouldReceive('increment')->with('fail', 1)->times(1);

        $driver->send($notification)->to([
            $this->makeSafariSubscriber(),
        ])->flush();
    }
}
