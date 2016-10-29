<?php

namespace Notimatica\Driver\Contracts;

interface SubscriberRepository
{
    /**
     * Return all subscribers.
     *
     * @param  int $limit
     * @param  int $offset
     * @return Subscriber[]
     */
    public function all($limit = 0, $offset = 0);

    /**
     * Subscribe to notifications.
     *
     * @param  string $provider
     * @param  array $data
     * @param  array $extra
     * @return Subscriber
     */
    public function subscribe($provider, array $data = [], array $extra = []);

    /**
     * Unsubscribe.
     *
     * @param  Subscriber $subscriber
     */
    public function unsubscribe(Subscriber $subscriber);
}
