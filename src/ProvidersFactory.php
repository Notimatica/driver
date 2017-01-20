<?php

namespace Notimatica\Driver;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
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
     * @param  array $options
     * @return AbstractProvider
     * @throws \RuntimeException For unsupported provider
     */
    public function make($name, array $options = [])
    {
        $provider = $this->resolveExtends($name, $options);

        if (is_null($provider)) {
            switch ($name) {
                case Chrome::NAME:
                    $provider = new Chrome($options);
                    break;
                case Firefox::NAME:
                    $provider = new Firefox($options);
                    break;
                case Safari::NAME:
                    $storage  = new Filesystem(new Local($options['assets']['root']));
                    $provider = new Safari($options);
                    $provider->setStorage($storage);
                    break;
                default:
                    throw new \RuntimeException("Unsupported provider '$name'");
            }
        }

        if (! $provider->isEnabled()) {
            throw new \RuntimeException("Provider '$name' is disabled");
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
}
