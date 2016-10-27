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
           return new FooProvider();
        });

        $this->assertInstanceOf(FooProvider::class, $factory->make(FooProvider::NAME));
    }
}

class FooProvider extends AbstractProvider
{
    const NAME = 'foo';

    /**
     * Send notification.
     *
     * @param  Notification $notification
     * @param  Subscriber[] $subscribers
     */
    public function send(Notification $notification, array $subscribers)
    {
        // TODO: Implement send() method.
    }

    /**
     * Distribute connection package.
     *
     * @param array $extra
     */
    public function connectionPackage($extra = [])
    {
        // TODO: Implement connectionPackage() method.
    }

    /**
     * Send request.
     *
     * @param  array $subscribers
     * @param  mixed $payload
     * @return \Generator
     */
    protected function prepareRequests($subscribers, $payload = null)
    {
        // TODO: Implement prepareRequests() method.
    }
}
