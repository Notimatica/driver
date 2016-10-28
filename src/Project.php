<?php

namespace Notimatica\Driver;

use Notimatica\Driver\Providers\AbstractProvider;

class Project
{
    /**
     * @var string
     */
    public $name = '';
    /**
     * @var array
     */
    public $config = [];
    /**
     * @var AbstractProvider[]
     */
    public $providers = [];
    /**
     * @var string
     */
    public $baseUrl;

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
        $this->config = $config;
        $this->baseUrl = $baseUrl;

        $this->buildProviders();
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
        $providersFactory = new ProvidersFactory($this);

        if (! empty($this->config['providers']) && is_array($this->config['providers'])) {
            foreach ($this->config['providers'] as $name => $options) {
                $this->providers[$name] = $providersFactory->make($name, $options)->setProject($this);
            }
        }
    }
}
