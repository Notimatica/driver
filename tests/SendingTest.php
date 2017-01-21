<?php

namespace Notimatica\Driver\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Notimatica\Driver\Apns\Streamer;
use Notimatica\Driver\Providers\Chrome;
use Notimatica\Driver\Providers\Firefox;

class SendingTest extends TestCase
{
    /** @test */
    public function test_notification_send_to_chrome()
    {
        $driver = $this->makeDriverWithMockClients(Chrome::NAME);

        $notification = $this->makeNotification();
        $notification->shouldReceive('wasSent')->once()->with(1);
        $notification->shouldReceive('wasFailed')->once()->with(1);

        $driver->send($notification)->to([
            $this->makeChromeSubscriber(),
        ])->flush();
    }

    /** @test */
    public function test_notification_send_to_firefox()
    {
        $driver = $this->makeDriverWithMockClients(Firefox::NAME);

        $notification = $this->makeNotification();
        $notification->shouldReceive('wasSent')->once()->with(1);

        $driver->send($notification)->to([
            $this->makeFirefoxSubscriber(),
        ])->flush();
    }

    /** @test */
    public function test_notification_send_to_safari()
    {
        $driver = $this->makeDriver();
        $provider = $driver->getProvider('safari');

        $streamer = m::mock(Streamer::class);
        $streamer->shouldReceive('write');
        $streamer->shouldReceive('close');
        $provider->setStreamer($streamer);

        $notification = $this->makeNotification();
        $notification->shouldReceive('wasFailed')->once()->with(1);

        $driver->send($notification)->to([
            $this->makeSafariSubscriber(),
        ])->flush();
    }

    /** @test */
    public function test_send_to_everyone()
    {
        $driver = $this->makeDriverWithMockClients(Chrome::NAME);
        $provider = $driver->getProvider('safari');

        $streamer = m::mock(Streamer::class);
        $streamer->shouldReceive('write');
        $streamer->shouldReceive('close');
        $provider->setStreamer($streamer);

        $notification = $this->makeNotification();
        $notification->shouldReceive('wasSent')->twice()->with(1);
        $notification->shouldReceive('wasFailed')->twice()->with(1);

        $driver->send($notification)->flush();
    }

    protected function makeDriverWithMockClients($provider)
    {
        $response = $provider == 'chrome'
            ? new Response(200, [], '{"success": 1}')
            : new Response(201);

        $driver = $this->makeDriver();
        $provider = $driver->getProvider($provider);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            $response,
        ]);

        $handler = HandlerStack::create($mock);

        $provider->setClient(new Client(['handler' => $handler]));

        return $driver;
    }
}
