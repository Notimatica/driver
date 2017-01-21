<?php

namespace Notimatica\Driver\Contracts;

interface Project
{
    /**
     * Returns project's name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns project's base url.
     *
     * @return string
     */
    public function getBaseUrl();

    /**
     * Returns project's config.
     *
     * @return array
     */
    public function getConfig();

    /**
     * Returns project's providers.
     *
     * @return array
     */
    public function getProviders();

    /**
     * Returns project's providers.
     *
     * @param  string $name
     * @return array
     */
    public function getProviderConfig($name);

    /**
     * Check if project has this provider.
     *
     * @param  string $name
     * @return bool
     */
    public function hasProvider($name);
}