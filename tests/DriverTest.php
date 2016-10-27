<?php namespace Notimatica\Driver\Tests;

use League\Event\Emitter;

class DriverTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_boot_events()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Emitter::class, $driver::$events);
    }

    /**
     * @test
     */
    public function it_can_split_subscribers()
    {
        $driver = $this->makeDriver();

        $driver->to([
           $this->makeChromeSubscriber()
        ]);

        $splitSubscribers = $this->getPublicMethod('splitSubscribers', $driver);
        $this->assertInternalType('array', $splitSubscribers->invoke($driver));
        $this->assertCount(1, $splitSubscribers->invoke($driver));
    }

    /**
     * @test
     */
    public function it_validates_input_data()
    {
        $driver = $this->makeDriver();

        $this->setExpectedException(\RuntimeException::class, "Notification wasn't set.");
        $driver->flush();

        $this->setExpectedException(\RuntimeException::class, "No subscribers set.");
        $driver->send($this->makeNotification())->flush();

        $driver->send($this->makeNotification())->to([])->flush();
    }
}