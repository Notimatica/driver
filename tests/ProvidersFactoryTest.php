<?php

namespace Notimatica\Driver\Tests;

use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Providers\AbstractProvider;
use Notimatica\Driver\Providers\Chrome;
use Notimatica\Driver\Providers\Firefox;
use Notimatica\Driver\Providers\Safari;
use Notimatica\Driver\ProvidersFactory;

class ProvidersFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_make_default_providers()
    {
        $factory = new ProvidersFactory($this->makeProject());

        $this->assertInstanceOf(Chrome::class, $factory->make(Chrome::NAME));
        $this->assertInstanceOf(Firefox::class, $factory->make(Firefox::NAME));
        $this->assertInstanceOf(Safari::class, $factory->make(Safari::NAME));
    }

    /**
     * @test
     */
    public function it_can_be_extended()
    {
        $factory = new ProvidersFactory($this->makeProject());

        ProvidersFactory::extend('foo', function () {
           return \Mockery::namedMock('FooProvider', AbstractProvider::class)->makePartial();
        });

        $this->assertInstanceOf('FooProvider', $factory->make('foo'));
    }
}
