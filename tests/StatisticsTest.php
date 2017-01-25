<?php

namespace Notimatica\Driver\Tests;

use League\Event\ListenerProviderInterface;
use Notimatica\Driver\StatisticsHandler;

class StatisticsTest extends TestCase
{
    /** @test */
    public function it_can_be_created()
    {
        $statistics = new StatisticsHandler($this->makeNotificationRepository());

        $this->assertInstanceOf(StatisticsHandler::class, $statistics);
        $this->assertInstanceOf(ListenerProviderInterface::class, $statistics);
    }
}
