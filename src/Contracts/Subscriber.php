<?php

namespace Notimatica\Driver\Contracts;

interface Subscriber
{
    const EXTRA_NAME_LENGTH  = 32;
    const EXTRA_VALUE_LENGTH = 100;

    /**
     * Returns unique subscriber's id.
     *
     * @return int|string
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

    /**
     * Destroy subscriber.
     */
    public function unsubscribe();
}
