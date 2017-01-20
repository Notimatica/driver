<?php

namespace Notimatica\Driver\Tests;

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
        $config = $this->getConfig('providers');
        $factory = new ProvidersFactory();

        $this->assertInstanceOf(Chrome::class, $factory->make(Chrome::NAME, $config[Chrome::NAME]));
        $this->assertInstanceOf(Firefox::class, $factory->make(Firefox::NAME, $config[Firefox::NAME]));
        $this->assertInstanceOf(Safari::class, $factory->make(Safari::NAME, $config[Safari::NAME]));
    }

    /**
     * @test
     */
    public function it_will_throw_unsupported_provider_exception()
    {
        $factory = new ProvidersFactory();

        $this->setExpectedException(\RuntimeException::class, "Unsupported provider '111'");
        $factory->make('111', []);
    }

    /**
     * @test
     */
    public function it_can_be_extended()
    {
        $factory = new ProvidersFactory();

        ProvidersFactory::extend('foo', function ($options) {
            $this->assertArrayHasKey('foo', $options);

            $mock = \Mockery::namedMock('FooProvider', AbstractProvider::class)->makePartial();
            $mock->shouldReceive('isEnabled')->andReturn(true);
            return $mock;
        });

        $this->assertInstanceOf('FooProvider', $factory->make('foo', ['foo' => 'bar']));
    }

    /**
     * @test
     */
    public function it_can_check_if_provider_is_enabled()
    {
        $factory = new ProvidersFactory();

        ProvidersFactory::extend('foo', function ($options) {
            $this->assertArrayHasKey('foo', $options);

            $mock = \Mockery::namedMock('FooProvider', AbstractProvider::class)->makePartial();
            $mock->shouldReceive('isEnabled')->andReturn(false);
            return $mock;
        });

        $this->setExpectedException(\RuntimeException::class);
        $factory->make('foo', ['foo' => 'bar']);
    }
}