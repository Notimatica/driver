<?php

namespace Notimatica\Driver\Tests;

use Notimatica\Driver\Providers\Chrome;
use Notimatica\Driver\Providers\Firefox;
use Notimatica\Driver\Providers\Safari;

class ProjectTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_boot_connected_providers()
    {
        $project = $this->makeProject();

        $this->assertTrue($project->providerConnected(Chrome::NAME));
        $this->assertTrue($project->providerConnected(Firefox::NAME));
        $this->assertTrue($project->providerConnected(Safari::NAME));
        $this->assertFalse($project->providerConnected('123'));
    }

    /**
     * @test
     */
    public function it_can_return_providers()
    {
        $project = $this->makeProject();

        $this->assertInternalType('array', $project->getProviders());
        $this->assertCount(3, $project->getProviders());

        $this->assertInstanceOf(Chrome::class, $project->getProvider(Chrome::NAME));
        $this->assertInstanceOf(Firefox::class, $project->getProvider(Firefox::NAME));
        $this->assertInstanceOf(Safari::class, $project->getProvider(Safari::NAME));

        $this->setExpectedException(\RuntimeException::class, "Unsupported provider 'foo'");
        $this->assertInstanceOf(Safari::class, $project->getProvider('foo'));
    }
}
