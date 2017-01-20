<?php

namespace Notimatica\Driver;

use GuzzleHttp\Client;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Notimatica\Driver\Apns\Certificate;
use Notimatica\Driver\Apns\Streamer;
use Notimatica\Driver\Providers\AbstractProvider;
use Notimatica\Driver\Providers\Chrome;
use Notimatica\Driver\Providers\Firefox;
use Notimatica\Driver\Providers\Safari;

class ProvidersFactory
{
    /**
     * @var \Closure[]
     */
    protected static $resolvers;

    /**
     * Extend resolver to support customers providers.
     *
     * @param string $name
     * @param \Closure $resolver
     */
    public static function extend($name, $resolver)
    {
        static::$resolvers[$name] = $resolver;
    }

    /**
     * Resolve provider from it's name.
     *
     * @param  string $name
     * @param  array $config
     * @return AbstractProvider
     * @throws \LogicException For unsupported provider
     */
    public function make($name, array $config = [])
    {
        $provider = $this->resolveExtends($name, $config);

        if (is_null($provider)) {
            $provider = $this->resolveProvider($name, $config);
        }

        if (! $provider->isEnabled()) {
            throw new \LogicException("Provider '$name' is disabled");
        }

        return $provider;
    }

    /**
     * Try to resolve extra providers.
     *
     * @param  string $name
     * @param  array $options
     * @return AbstractProvider|null
     */
    protected function resolveExtends($name, array $options)
    {
        if (empty(static::$resolvers[$name])) {
            return;
        }

        return call_user_func(static::$resolvers[$name], $options);
    }

    /**
     * Make Chrome provider.
     *
     * @param  array $options
     * @return Chrome
     */
    protected function makeChromeProvider(array $options)
    {
        $client = new Client([
            'timeout' => isset($config['timeout']) ? $config['timeout'] : Chrome::DEFAULT_TIMEOUT,
        ]);

        return new Chrome($options, $client);
    }

    /**
     * Make Firefox provider.
     *
     * @param  array $config
     * @return Firefox
     */
    protected function makeFirefoxProvider(array $config)
    {
        $client = new Client([
            'timeout' => isset($config['timeout']) ? $config['timeout'] : Firefox::DEFAULT_TIMEOUT,
        ]);

        return new Firefox($config, $client);
    }

    /**
     * Make Safari provider.
     *
     * @param  array $config
     * @return Safari
     */
    protected function makeSafariProvider(array $config)
    {
        $storage = new Filesystem(new Local($config['assets']['root']));
        $certificate = new Certificate($config['assets']['certificates'], $storage);
        $streamer = new Streamer($certificate, $config['service_url']);

        return new Safari($config, $storage, $streamer);
    }

    /**
     * Resolve supported provider.
     *
     * @param  string $name
     * @param  array $config
     * @return AbstractProvider
     * @throws \InvalidArgumentException
     */
    protected function resolveProvider($name, array $config)
    {
        switch ($name) {
            case Chrome::NAME:
                return $this->makeChromeProvider($config);
            case Firefox::NAME:
                return $this->makeFirefoxProvider($config);
            case Safari::NAME:
                return $this->makeSafariProvider($config);
            default:
                throw new \InvalidArgumentException("Unsupported provider '$name'");
        }
    }
}
