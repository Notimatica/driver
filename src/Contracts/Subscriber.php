<?php namespace Notimatica\Driver\Contracts;

interface Subscriber
{
    /**
     * Returns unique subscriber's uuid.
     *
     * @return string
     */
    public function getUuid();

    /**
     * Returns unique subscriber's uuid.
     *
     * @return string
     */
    public function getProvider();

    /**
     * Returns notifications provider's token.
     *
     * @return string
     */
    public function getToken();
}