<?php

namespace Notimatica\Driver\Contracts;

use Notimatica\Driver\Providers\AbstractProvider;

interface Project
{
    /**
     * Make providers.
     *
     * @return AbstractProvider[]
     */
    public function getProviders();

    /**
     * Fetch connected provider.
     *
     * @param  string $name
     * @return AbstractProvider
     */
    public function getProvider($name);

    /**
     * Check if project has required provider.
     *
     * @param  string $name
     * @return bool
     */
    public function providerConnected($name);
}