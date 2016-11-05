<?php

namespace Notimatica\Driver\Contracts;

interface Notification
{
    /**
     * Returns unique notification's id.
     *
     * @return int|string
     */
    public function getId();

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
     * Returns notification's onclick url.
     *
     * @return string
     */
    public function getUrl();

    /**
     * Delete notification.
     */
    public function delete();
}
