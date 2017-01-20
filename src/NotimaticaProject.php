<?php

namespace Notimatica\Driver;

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
        $this->config = $config;;
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
     * Returns project's providers.
     *
     * @return array
     */
    public function getProviders()
    {
        return ! empty($this->config['providers']) && is_array($this->config['providers'])
            ? array_keys($this->config['providers'])
            : [];
    }

    /**
     * Returns project's providers.
     *
     * @param  string $name
     * @return array
     */
    public function getProviderConfig($name)
    {
        return $this->config['providers'][$name];
    }
}
