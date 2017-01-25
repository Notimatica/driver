<?php

namespace Notimatica\Driver\Tests;

use League\Event\Emitter;
use Mockery as m;
use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\NotificationRepository;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Contracts\SubscriberRepository;
use Notimatica\Driver\Driver;
use Notimatica\Driver\NotimaticaProject;
use Notimatica\Driver\PayloadStorage;
use Notimatica\Driver\Providers\AbstractProvider;
use Notimatica\Driver\Providers\Chrome;
use Notimatica\Driver\Providers\Firefox;
use Notimatica\Driver\Providers\Safari;
use Notimatica\Driver\ProvidersFactory;
use Notimatica\Driver\StatisticsHandler;
use ReflectionClass;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $config;

    protected function setUp()
    {
        $this->config = Driver::getConfig();
        $this->setConfig(
            'providers.' . Safari::NAME . '.assets.root',
            __DIR__ . '/tmp/safari_push_data'
        );
        $this->setConfig(
            'providers.' . Safari::NAME . '.website_push_id',
            '111222333'
        );
        $this->setConfig(
            'providers.' . Chrome::NAME . '.sender_id',
            '111222333'
        );
        $this->setConfig(
            'providers.' . Chrome::NAME . '.api_key',
            'foobar'
        );

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->config = null;
        \Mockery::close();

        parent::tearDown();
    }

    /**
     * Make protected/private class property accessible.
     *
     * @param  string|object $class
     * @param  string $name
     * @return \ReflectionProperty
     */
    protected function getPublicProperty($class, $name)
    {
        if (! is_string($class)) {
            $class = get_class($class);
        }

        $class    = new ReflectionClass($class);
        $property = $class->getProperty($name);
        $property->setAccessible(true);

        return $property;
    }
    /**
     * Make protected/private class method accessible.
     *
     * @param  string $name
     * @param  string|object $class
     * @return \ReflectionMethod
     */
    protected function getPublicMethod($name, $class)
    {
        if (! is_string($class)) {
            $class = get_class($class);
        }

        $class  = new ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Returns config.
     *
     * @param  string $key
     * @return mixed
     */
    protected function getConfig($key = null)
    {
        if (is_null($key)) {
            return $this->config;
        }

        $config = $this->config;
        foreach (explode('.', $key) as $step) {
            $config = $config[$step];
        }

        return $config;
    }

    /**
     * Set config value.
     *
     * @param  string $key
     * @param  mixed $value
     */
    protected function setConfig($key, $value)
    {
        $config = &$this->config;
        foreach (explode('.', $key) as $step) {
            $config = &$config[$step];
        }

        $config = $value;

        unset($config);
    }

    /**
     * Make project.
     *
     * @return NotimaticaProject
     */
    protected function makeProject()
    {
        return new NotimaticaProject('Test Project', 'https://localhost', 'https://localhost/icon.png', $this->getConfig());
    }

    /**
     * Make driver.
     *
     * @return Driver
     */
    protected function makeDriver()
    {
        return new Driver(
            $this->makeProject(),
            new Emitter(),
            $this->makeNotificationRepository(),
            $this->makeSubscriberRepository(),
            $this->makePayloadStorage(),
            $this->makeStatistics()
        );
    }

    /**
     * Make provider.
     *
     * @param  string $provider
     * @return AbstractProvider
     */
    protected function makeProvider($provider)
    {
        $factory = new ProvidersFactory($this->makeProject());

        return $factory->resolveProvider($provider);
    }

    /**
     * @return StatisticsHandler
     */
    protected function makeStatistics()
    {
        return new StatisticsHandler();
    }

    /**
     * @return \Mockery\MockInterface|PayloadStorage
     */
    protected function makePayloadStorage()
    {
        $storage = m::mock(PayloadStorage::class)->makePartial();
        $storage->shouldReceive('assignPayloadToSubscriber');

        return $storage;
    }

    /**
     * @return \Mockery\MockInterface|NotificationRepository
     */
    protected function makeNotificationRepository()
    {
        $repository = m::mock(NotificationRepository::class);
        $repository->shouldReceive('all')->andReturn([
            $this->makeNotification(),
        ]);
        $repository->shouldReceive('make')->andReturn($this->makeNotification());

        return $repository;
    }

    /**
     * @return \Mockery\MockInterface|Notification
     */
    protected function makeNotification()
    {
        $notification = m::mock(Notification::class);
        $notification->shouldReceive('getId')->andReturn('05350612-c647-41e0-acbe-8d3eb0a19855');
        $notification->shouldReceive('getTitle')->andReturn('Test title');
        $notification->shouldReceive('getBody')->andReturn('Notification body with long long long long long long long long long long long body to trim.');
        $notification->shouldReceive('getUrl')->andReturn('/click');

        return $notification;
    }

    /**
     * @return \Mockery\MockInterface|SubscriberRepository
     */
    protected function makeSubscriberRepository()
    {
        $repository = m::mock(SubscriberRepository::class);
        $repository->shouldReceive('all')->andReturn([
            $this->makeChromeSubscriber(),
            $this->makeFirefoxSubscriber(),
            $this->makeSafariSubscriber(),
        ]);
        $repository->shouldReceive('find')->andReturn($this->makeChromeSubscriber());
        $repository->shouldReceive('findByToken')->andReturn($this->makeChromeSubscriber());
        $repository->shouldReceive('subscribe')->andReturn($this->makeChromeSubscriber());

        return $repository;
    }

    /**
     * @param string $id
     * @param string $token
     * @return \Mockery\MockInterface|Subscriber
     */
    protected function makeChromeSubscriber($id = '2', $token = '111122223333qqqwweee')
    {
        $subscriber = m::mock(Subscriber::class);
        $subscriber->shouldReceive('getId')->andReturn($id);
        $subscriber->shouldReceive('getProvider')->andReturn(Chrome::NAME);
        $subscriber->shouldReceive('getToken')->andReturn($token);

        return $subscriber;
    }

    /**
     * @param string $id
     * @param string $token
     * @return \Mockery\MockInterface|Subscriber
     */
    protected function makeFirefoxSubscriber($id = 'a060f737-a83a-465a-bcc9-26e5c4a2cea4', $token = '111122223333qqqwweee')
    {
        $subscriber = m::mock(Subscriber::class);
        $subscriber->shouldReceive('getId')->andReturn($id);
        $subscriber->shouldReceive('getProvider')->andReturn(Firefox::NAME);
        $subscriber->shouldReceive('getToken')->andReturn($token);

        return $subscriber;
    }

    /**
     * @param string $id
     * @param string $token
     * @return \Mockery\MockInterface|Subscriber
     */
    protected function makeSafariSubscriber($id = 'a060f737-a83a-465a-bcc9-26e5c4a2cea4', $token = '111122223333444aaabbb')
    {
        $subscriber = m::mock(Subscriber::class);
        $subscriber->shouldReceive('getId')->andReturn($id);
        $subscriber->shouldReceive('getProvider')->andReturn(Safari::NAME);
        $subscriber->shouldReceive('getToken')->andReturn($token);

        return $subscriber;
    }
}
