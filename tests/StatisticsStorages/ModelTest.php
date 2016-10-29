<?php

namespace Notimatica\Driver\Tests\Statistics\Storages;

use Notimatica\Driver\StatisticsStorages\Model;
use Notimatica\Driver\StatisticsStoragesFactory;
use Notimatica\Driver\Tests\TestCase;

class ModelTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_made()
    {
        $factory = new StatisticsStoragesFactory();

        $this->assertInstanceOf(Model::class, $factory->make(Model::NAME));
    }
}