<?php

namespace Notimatica\Driver\Contracts;

interface NotificationRepository
{
    /**
     * Return all notifications.
     *
     * @param  int $limit
     * @param  int $offset
     * @return Notification[]
     */
    public function all($limit = 0, $offset = 0);

    /**
     * Make notification.
     *
     * @param  string $title
     * @param  string $body
     * @param  string $url
     * @return Notification
     */
    public function make($title, $body, $url);
}
