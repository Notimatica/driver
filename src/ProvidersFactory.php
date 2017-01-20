<?php

namespace Notimatica\Driver;

use GuzzleHttp\Client;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Notimatica\Driver\Apns\Certificate;
use Notimatica\Driver\Apns\Streamer;
use Notimatica\Driver\Contracts\Project;
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
     * @var Project
     */
    protected $project;

    /**
     * ProvidersFactory constructor.
     *
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

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
     * @return AbstractProvider
     * @throws \LogicException For unsupported provider
     */
    public function make($name)
    {
        $provider = $this->resolveExtends($name);

        if (is_null($provider)) {
            $provider = $this->resolveProvider($name);
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
     * @return AbstractProvider|null
     */
    protected function resolveExtends($name)
    {
        if (empty(static::$resolvers[$name])) {
            return;
        }

        return call_user_func(static::$resolvers[$name], $this->project);
    }

    /**
     * Make Chrome provider.
     *
     * @return Chrome
     */
    protected function makeChromeProvider()
    {
        $config = $this->project->getProviderConfig(Chrome::NAME);

        $client = new Client([
            'timeout' => isset($config['timeout']) ? $config['timeout'] : Chrome::DEFAULT_TIMEOUT,
        ]);

        return new Chrome($this->project, $client);
    }

    /**
     * Make Firefox provider.
     *
     * @return Firefox
     */
    protected function makeFirefoxProvider()
    {
        $config = $this->project->getProviderConfig(Firefox::NAME);

        $client = new Client([
            'timeout' => isset($config['timeout']) ? $config['timeout'] : Firefox::DEFAULT_TIMEOUT,
        ]);

        return new Firefox($this->project, $client);
    }

    /**
     * Make Safari provider.
     *
     * @return Safari
     */
    protected function makeSafariProvider()
    {
        $config = $this->project->getProviderConfig(Safari::NAME);

        $storage = new Filesystem(new Local($config['assets']['root']));
        $certificate = new Certificate($config['assets']['certificates'], $storage);
        $streamer = new Streamer($certificate, $config['service_url']);

        return new Safari($this->project, $storage, $streamer);
    }

    /**
     * Resolve supported provider.
     *
     * @param  string $name
     * @return AbstractProvider
     * @throws \InvalidArgumentException
     */
    protected function resolveProvider($name)
    {
        $method = 'make' . ucfirst($name) . 'Provider';
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method]);
        }

        throw new \InvalidArgumentException("Unsupported provider '$name'");
    }
}
