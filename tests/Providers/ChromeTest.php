<?php namespace Notimatica\Driver\Tests\Providers;

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
            'name' => $this->makeProject()->name,
            'gcm_sender_id' => '111222333'
        ]));
    }
}