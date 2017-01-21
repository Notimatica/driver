<?php

namespace Notimatica\Driver\Tests;

use Notimatica\Driver\Contracts\Project;
use Notimatica\Driver\Providers\AbstractProvider;
use Notimatica\Driver\Providers\Chrome;
use Notimatica\Driver\Providers\Firefox;
use Notimatica\Driver\Providers\Safari;
use Notimatica\Driver\ProvidersFactory;

class ProvidersFactoryTest extends TestCase
{
    /** @test */
    public function it_can_make_default_providers()
    {
        $factory = new ProvidersFactory($this->makeProject());

        $this->assertInstanceOf(Chrome::class, $factory->resolveProvider(Chrome::NAME));
        $this->assertInstanceOf(Firefox::class, $factory->resolveProvider(Firefox::NAME));
        $this->assertInstanceOf(Safari::class, $factory->resolveProvider(Safari::NAME));
    }

    /** @test */
    public function it_can_save_already_resolved_providers()
    {
        $factory = new ProvidersFactory($this->makeProject());
        $provider1 = $factory->make(Chrome::NAME);
        $provider2 = $factory->make(Chrome::NAME);

        $this->assertSame($provider1, $provider2);
    }

    /** @test */
    public function it_will_throw_unsupported_provider_exception()
    {
        $factory = new ProvidersFactory($this->makeProject());

        $this->setExpectedException(\LogicException::class, "Unsupported provider '111'");
        $factory->resolveProvider('111');
    }

    /** @test */
    public function it_can_be_extended()
    {
        $factory = new ProvidersFactory($this->makeProject());

        ProvidersFactory::extend('foo', function (Project $project) {
            $mock = \Mockery::namedMock('FooProvider', AbstractProvider::class);
            $mock->shouldReceive('isEnabled')->andReturn(true);

            return $mock;
        });

        $this->assertInstanceOf('FooProvider', $factory->resolveProvider('foo'));
    }

    /** @test */
    public function it_can_check_if_provider_is_enabled()
    {
        $factory = new ProvidersFactory($this->makeProject());

        ProvidersFactory::extend('foo', function (Project $project) {
            $mock = \Mockery::namedMock('FooProvider', AbstractProvider::class);
            $mock->shouldReceive('isEnabled')->andReturn(false);

            return $mock;
        });

        $this->setExpectedException(\LogicException::class, "Provider 'foo' is not enabled");
        $factory->resolveProvider('foo');
    }
}