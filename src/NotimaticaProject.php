<?php

namespace Notimatica\Driver;

use Notimatica\Driver\Providers\AbstractProvider;

class NotimaticaProject implements \Notimatica\Driver\Contracts\Project
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $baseUrl;
    /**
     * @var array
     */
    public $config = [];
    /**
     * @var AbstractProvider[]
     */
    public $providers = [];

    /**
     * Create a new Project.
     *
     * @param string $name
     * @param string $baseUrl
     * @param array $config
     */
    public function __construct($name, $baseUrl, array $config = [])
    {
        $this->name = $name;
        $this->baseUrl = $baseUrl;
        $this->config = $config;

        $this->buildProviders();
    }

    /**
     * Returns project's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns project's base url.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Returns project's config.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Make providers.
     *
     * @return AbstractProvider[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Fetch connected provider.
     *
     * @param  string $name
     * @return AbstractProvider
     * @throws \RuntimeException If provider isn't connected
     */
    public function getProvider($name)
    {
        if (! $this->providerConnected($name)) {
            throw new \RuntimeException("Unsupported provider '{$name}'");
        }

        return $this->providers[$name];
    }

    /**
     * Check if project has required provider.
     *
     * @param  string $name
     * @return bool
     */
    public function providerConnected($name)
    {
        return array_key_exists($name, $this->providers);
    }

    /**
     * Build providers objects.
     */
    public function buildProviders()
    {
        $providersFactory = new ProvidersFactory();

        if (! empty($this->config['providers']) && is_array($this->config['providers'])) {
            foreach ($this->config['providers'] as $name => $options) {
                try {
                    $this->providers[$name] = $providersFactory->make($name, $options)->setProject($this);
                } catch (\RuntimeException $e) {}
            }
        }
    }
}
