<?php

namespace Notimatica\Driver\Tests\Providers;

use GuzzleHttp\Psr7\Request;
use Notimatica\Driver\Providers\Chrome;
use Notimatica\Driver\Tests\TestCase;

class ChromeTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_calculate_chunk_size()
    {
        $this->setConfig('providers.' . Chrome::NAME . '.batch_chunk_size', 500);
        $provider = $this->makeProvider(Chrome::NAME);
        $calculateChunkSize = $this->getPublicMethod('calculateChunkSize', $provider);

        // Chunk size in config: 1000

        $this->assertEquals(500, $calculateChunkSize->invoke($provider, 0, 1400));
        $this->assertEquals(500, $calculateChunkSize->invoke($provider, 1, 1400));
        $this->assertEquals(400, $calculateChunkSize->invoke($provider, 2, 1400));
        $this->assertEquals(0, $calculateChunkSize->invoke($provider, 3, 1400));
    }

    /**
     * @test
     */
    public function it_can_generate_manifest()
    {
        $this->setConfig('providers.' . Chrome::NAME . '.sender_id', '111222333');
        $provider = $this->makeProvider(Chrome::NAME);

        $manifest = $provider->manifest();

        $this->assertJson($manifest);
        $this->assertJsonStringEqualsJsonString($manifest, json_encode([
            'name' => $this->makeProject()->getName(),
            'gcm_sender_id' => '111222333',
        ]));
    }

    /**
     * @test
     */
    public function it_can_generate_request_body_for_chunk()
    {
        $provider = $this->makeProvider(Chrome::NAME);

        $getRequestContent = $this->getPublicMethod('getRequestContent', $provider);
        $content = $getRequestContent->invoke($provider, $this->prepareSubscribers());
        $this->assertJson($content);
        $this->assertJsonStringEqualsJsonString($content, json_encode([
            'registration_ids' => [
                '123123',
                'qweqwe',
                'asdasd',
            ],
        ]));
    }

    /**
     * @test
     */
    public function it_can_create_concurrent_requests()
    {
        $this->setConfig('providers.' . Chrome::NAME . '.batch_chunk_size', 2);
        $provider = $this->makeProvider(Chrome::NAME);

        $prepareRequests = $this->getPublicMethod('prepareRequests', $provider);
        $generator = $prepareRequests->invoke($provider, $this->prepareSubscribers());
        $requests  = iterator_to_array($generator);

        $this->assertInstanceOf(\Generator::class, $generator);
        $this->assertCount(2, $requests);
        $this->assertInstanceOf(Request::class, $requests[0]);
        $this->assertInstanceOf(Request::class, $requests[1]);
    }

    private function prepareSubscribers()
    {
        return [
            $this->makeChromeSubscriber('111', '123123'),
            $this->makeChromeSubscriber('222', 'qweqwe'),
            $this->makeChromeSubscriber('333', 'asdasd'),
        ];
    }
}
