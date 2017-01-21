<?php

namespace Notimatica\Driver\Tests;

use League\Event\Emitter;
use League\Event\Event;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Driver;
use Notimatica\Driver\NotimaticaProject;
use Notimatica\Driver\Providers\Chrome;
use Notimatica\Driver\Providers\Firefox;
use Notimatica\Driver\Providers\Safari;

class DriverTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_boot_events()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Emitter::class, $driver::$emitter);
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
    public function it_can_boot_connected_providers()
    {
        $driver = $this->makeDriver();

        $this->assertTrue($driver->providerConnected(Chrome::NAME));
        $this->assertTrue($driver->providerConnected(Firefox::NAME));
        $this->assertTrue($driver->providerConnected(Safari::NAME));
        $this->assertFalse($driver->providerConnected('123'));
    }

    /**
     * @test
     */
    public function it_can_return_providers()
    {
        $driver = $this->makeDriver();

        $this->assertInstanceOf(Chrome::class, $driver->getProvider(Chrome::NAME));
        $this->assertInstanceOf(Firefox::class, $driver->getProvider(Firefox::NAME));
        $this->assertInstanceOf(Safari::class, $driver->getProvider(Safari::NAME));

        $this->setExpectedException(\InvalidArgumentException::class, "Unsupported provider 'foo'");
        $this->assertInstanceOf(Safari::class, $driver->getProvider('foo'));
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

        $driver->send($this->makeNotification());

        $splitSubscribers = $this->getPublicMethod('splitSubscribers', $driver);
        $partials = $splitSubscribers->invoke($driver, [
            $this->makeChromeSubscriber(),
            $unknownProviderSubscriber,
        ]);

        $this->assertInternalType('array', $partials);
        $this->assertCount(1, $partials);
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
        $this->assertInstanceOf(NotimaticaProject::class, $driver->getProject());

        $driver->setProject(\Mockery::namedMock('FooProject', NotimaticaProject::class)->makePartial());
        $this->assertInstanceOf('FooProject', $driver->getProject());
    }
}
