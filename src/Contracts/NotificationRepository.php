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
     * @return Notification
     */
    public function make();
}
