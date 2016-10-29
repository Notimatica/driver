<?php

namespace Notimatica\Driver\Contracts;

interface Subscriber
{
    /**
     * Returns unique subscriber's id.
     *
     * @return string
     */
    public function getId();

    /**
     * Returns subscriber's provider.
     *
     * @return string
     */
    public function getProvider();

    /**
     * Returns subscriber's provider token.
     *
     * @return string
     */
    public function getToken();
}
