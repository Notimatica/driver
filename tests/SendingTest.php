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
        $notification->shouldReceive('increment')->with('clicked', 1);

        $driver->send($notification)->to([
            $this->makeChromeSubscriber(),
        ])->flush();
    }
}
