<?php

namespace Notimatica\Driver\Contracts;

interface Notification
{
    /**
     * Returns unique notification's uuid.
     *
     * @return string
     */
    public function getUuid();

    /**
     * Returns notification's title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Returns notification's body.
     *
     * @return string
     */
    public function getBody();

    /**
     * Increments attribute value.
     *
     * @param  string $column
     * @param  int $amount
     */
    public function increment($column, $amount = 1);
}