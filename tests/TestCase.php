<?php

namespace Notimatica\Driver\Tests;

use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Driver;
use Notimatica\Driver\Project;
use Notimatica\Driver\Providers\AbstractProvider;
use Notimatica\Driver\Providers\Chrome;
use Notimatica\Driver\Providers\Firefox;
use Notimatica\Driver\Providers\Safari;
use Notimatica\Driver\ProvidersFactory;
use ReflectionClass;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $config;

    protected function setUp()
    {
        $this->config = require __DIR__ . '/../src/config/notimatica.php';
        $this->setConfig(
            'providers.' . Safari::NAME . '.storage_root',
            __DIR__ . '/tmp/safari_push_data'
        );

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->config = null;

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
        if (is_null($key)) return $this->config;

        $config = $this->config;
        foreach(explode('.', $key) as $step)
        {
            $config = $config[$step];
        }

        return $config;
    }

    /**
     * Set config value.
     *
     * @param  string $key
     * @param  mixed $value
     * @return mixed
     */
    protected function setConfig($key, $value)
    {
        $config = &$this->config;
        foreach(explode('.', $key) as $step)
        {
            $config = &$config[$step];
        }

        return $config = $value;
    }

    /**
     * Make project.
     *
     * @return Project
     */
    protected function makeProject()
    {
        return new Project('Test Project', 'http://localhost', $this->getConfig());
    }

    /**
     * Make driver.
     *
     * @return Driver
     */
    protected function makeDriver()
    {
        return new Driver($this->makeProject());
    }

    /**
     * Make provider.
     *
     * @param  string $provider
     * @return AbstractProvider
     */
    protected function makeProvider($provider)
    {
        $config = $this->getConfig('providers');
        $factory = new ProvidersFactory();

        return $factory->make($provider, $config[$provider])
            ->setProject($this->makeProject());
    }

    /**
     * @return \Mockery\MockInterface|Notification
     */
    protected function makeNotification()
    {
        $notification = \Mockery::mock(Notification::class);
        $notification->shouldReceive('getUuid')->andReturn('05350612-c647-41e0-acbe-8d3eb0a19855');
        $notification->shouldReceive('getTitle')->andReturn('Test title');
        $notification->shouldReceive('getBody')->andReturn('Notification body with long long long long long long long long long long long body to trim.');

        return $notification;
    }

    /**
     * @param string $uuid
     * @param string $token
     * @return \Mockery\MockInterface|Subscriber
     */
    protected function makeChromeSubscriber($uuid = 'a060f737-a83a-465a-bcc9-26e5c4a2cea4', $token = '111122223333qqqwweee')
    {
        $subscriber = \Mockery::mock(Subscriber::class);
        $subscriber->shouldReceive('getUuid')->andReturn($uuid);
        $subscriber->shouldReceive('getProvider')->andReturn(Chrome::NAME);
        $subscriber->shouldReceive('getToken')->andReturn($token);

        return $subscriber;
    }

    /**
     * @param string $uuid
     * @param string $token
     * @return \Mockery\MockInterface|Subscriber
     */
    protected function makeFirefoxSubscriber($uuid = 'a060f737-a83a-465a-bcc9-26e5c4a2cea4', $token = '111122223333qqqwweee')
    {
        $subscriber = \Mockery::mock(Subscriber::class);
        $subscriber->shouldReceive('getUuid')->andReturn($uuid);
        $subscriber->shouldReceive('getProvider')->andReturn(Firefox::NAME);
        $subscriber->shouldReceive('getToken')->andReturn($token);

        return $subscriber;
    }

    /**
     * @param string $uuid
     * @param string $token
     * @return \Mockery\MockInterface|Subscriber
     */
    protected function makeSafariSubscriber($uuid = 'a060f737-a83a-465a-bcc9-26e5c4a2cea4', $token = '111122223333444aaabbb')
    {
        $subscriber = \Mockery::mock(Subscriber::class);
        $subscriber->shouldReceive('getUuid')->andReturn($uuid);
        $subscriber->shouldReceive('getProvider')->andReturn(Safari::NAME);
        $subscriber->shouldReceive('getToken')->andReturn($token);

        return $subscriber;
    }
}
