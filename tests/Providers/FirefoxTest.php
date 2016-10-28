<?php namespace Notimatica\Driver\Tests\Providers;

use GuzzleHttp\Psr7\Request;
use Notimatica\Driver\Providers\Firefox;
use Notimatica\Driver\Tests\TestCase;

class FirefoxTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_create_concurrent_requests()
    {
        $provider = $this->makeProvider(Firefox::NAME);

        $prepareRequests = $this->getPublicMethod('prepareRequests', $provider);
        $generator = $prepareRequests->invoke($provider, $this->prepareSubscribers());
        $requests  = iterator_to_array($generator);

        $this->assertInstanceOf(\Generator::class, $generator);
        $this->assertCount(3, $requests);
        $this->assertInstanceOf(Request::class, $requests[0]);
        $this->assertInstanceOf(Request::class, $requests[1]);
        $this->assertInstanceOf(Request::class, $requests[2]);
    }

    private function prepareSubscribers()
    {
        return [
            $this->makeFirefoxSubscriber('111', '123123'),
            $this->makeFirefoxSubscriber('222', 'qweqwe'),
            $this->makeFirefoxSubscriber('333', 'asdasd'),
        ];
    }
}