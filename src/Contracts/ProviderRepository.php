<?php namespace Notimatica\Driver\Contracts;

interface ProviderRepository
{
    /**
     * Makes provider via it's slug.
     *
     * @param  string $slug
     * @return Provider
     */
    public function getProvider($slug);
}