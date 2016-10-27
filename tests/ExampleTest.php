<?php

namespace Notimatica\Driver\Tests;

use Notimatica\Driver\Driver;
use Notimatica\Driver\Project;

class ExampleTest extends TestCase
{
    /**
     * @test
     */
    public function test_notification_send_to_chrome()
    {
        $project = new Project('Test', $this->config);
        $driver = new Driver($project);

        $notification = $this->makeNotification();
        $notification->shouldReceive('increment')->with('clicked', 1);

        $driver->send($notification)->to([
            $this->makeChromeSubscriber()
        ])->flush();
    }
}