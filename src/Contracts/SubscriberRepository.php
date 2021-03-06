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
     * Find subscriber by id.
     *
     * @param  int|string $id
     * @return Subscriber|null
     */
    public function find($id);

    /**
     * Find subscriber by id.
     *
     * @param  string $token
     * @return Subscriber|null
     */
    public function findByToken($token);

    /**
     * Subscribe to notifications.
     *
     * @param  string $provider
     * @param  string $token
     * @param  array $env
     * @param  array $extra
     * @return Subscriber
     */
    public function make($provider, $token, array $env = [], array $extra = []);
}
