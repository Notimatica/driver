<?php

namespace Notimatica\Driver\Tests;

use League\Event\Emitter;
use League\Event\Event;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Driver;
use Notimatica\Driver\Project;
use Notimatica\Driver\Providers\AbstractProvider;
use Notimatica\Driver\ProvidersFactory;

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
    public function it_can_listen_events()
    {
        Driver::on('foo-event', function (Event $event, $var) {
            $this->assertEquals('bar', $var);
        });

        Driver::emit('foo-event', 'bar');

        Driver::off('foo-event');
    }

    /**
     * @test
     */
    public function it_can_split_subscribers()
    {
        $driver = $this->makeDriver();

        $unknownProviderSubscriber = \Mockery::mock(Subscriber::class);
        $unknownProviderSubscriber->shouldReceive('getUuid')->andReturn('1111');
        $unknownProviderSubscriber->shouldReceive('getProvider')->andReturn('123');
        $unknownProviderSubscriber->shouldReceive('getToken')->andReturn('2222');

        $driver->send($this->makeNotification())->to([
            $this->makeChromeSubscriber(),
            $unknownProviderSubscriber,
        ]);

        $splitSubscribers = $this->getPublicMethod('splitSubscribers', $driver);
        $this->assertInternalType('array', $splitSubscribers->invoke($driver));
        $this->assertCount(1, $splitSubscribers->invoke($driver));
    }

    /**
     * @test
     */
    public function it_validates_subscribers_input()
    {
        $driver = $this->makeDriver();
        $this->setExpectedException(\RuntimeException::class, 'No subscribers set.');
        $driver->send($this->makeNotification())->flush();
    }

    /**
     * @test
     */
    public function it_validates_notification_input()
    {
        $driver = $this->makeDriver();
        $this->setExpectedException(\RuntimeException::class, "Notification wasn't set.");
        $driver->to([])->flush();
    }

    /**
     * @test
     */
    public function it_can_set_and_return_project()
    {
        $driver = $this->makeDriver();
        $this->assertInstanceOf(Project::class, $driver->getProject());

        $driver->setProject(\Mockery::namedMock('FooProject', Project::class)->makePartial());
        $this->assertInstanceOf('FooProject', $driver->getProject());
    }
}
