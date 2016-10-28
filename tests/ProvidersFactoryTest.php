<?php

namespace Notimatica\Driver\Tests;

use Notimatica\Driver\ProvidersFactory;
use Notimatica\Driver\StatisticsStorages\AbstractStorage;
use Notimatica\Driver\StatisticsStorages\Model;
use Notimatica\Driver\StatisticsStoragesFactory;

class StatisticsStoragFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_make_default_storage()
    {
        $factory = new StatisticsStoragesFactory();

        $this->assertInstanceOf(Model::class, $factory->make(Model::NAME));
    }

    /**
     * @test
     */
    public function it_will_throw_unsupported_provider_exception()
    {
        $factory = new StatisticsStoragesFactory();

        $this->setExpectedException(\RuntimeException::class, "Unsupported statistics storage '111'");
        $factory->make('111');
    }

    /**
     * @test
     */
    public function it_can_be_extended()
    {
        $factory = new StatisticsStoragesFactory();

        StatisticsStoragesFactory::extend('foo', function () {
            return \Mockery::namedMock('FooStorage', AbstractStorage::class)->makePartial();
        });

        $this->assertInstanceOf('FooStorage', $factory->make('foo'));
    }
}
