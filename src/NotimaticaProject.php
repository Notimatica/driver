<?php

namespace Notimatica\Driver;

use Notimatica\Driver\Contracts\Project;

class NotimaticaProject implements Project
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
     * @var string
     */
    private $icon;
    /**
     * @var array
     */
    public $config = [];

    /**
     * Create a new Project.
     *
     * @param string $name
     * @param string $baseUrl
     * @param string $icon
     * @param array $config
     */
    public function __construct($name, $baseUrl, $icon, array $config = [])
    {
        $this->name = $name;
        $this->baseUrl = $baseUrl;
        $this->icon = $icon;
        $this->config = $config;
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
     * Returns project's base icon.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
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
     * Check if project has this provider.
     *
     * @param  string $name
     * @return bool
     */
    public function hasProvider($name)
    {
        return in_array($name, $this->getProviders());
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
        return $this->hasProvider($name)
            ? $this->config['providers'][$name]
            : [];
    }
}
