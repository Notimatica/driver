<?php

namespace Notimatica\Driver\Tests;

use League\Event\ListenerProviderInterface;
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
}