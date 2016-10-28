<?php

namespace Notimatica\Driver\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Notimatica\Driver\Contracts\FilesStorage;
use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Project;

abstract class AbstractProvider
{
    const NAME = null;

    /**
     * @var Project
     */
    protected $project;
    /**
     * @var FilesStorage
     */
    protected $fileStorage;
    /**
     * @var array
     */
    protected $config = [];
    /**
     * @var Client
     */
    protected static $browser;

    /**
     * Create a new Provider.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Set project.
     *
     * @param  Project $project
     * @return $this
     */
    public function setProject(Project $project)
    {
        $this->project  = $project;

        return $this;
    }

    /**
     * Set files storage.
     *
     * @param  FilesStorage $filesStorage
     * @return $this
     */
    public function setStorage(FilesStorage $filesStorage)
    {
        $this->fileStorage = $filesStorage;

        return $this;
    }

    /**
     * Send notification.
     *
     * @param  Notification $notification
     * @param  Subscriber[] $subscribers
     */
    abstract public function send(Notification $notification, array $subscribers);

    /**
     * Send request.
     *
     * @param  array $subscribers
     * @param  mixed $payload
     * @return \Generator
     */
    abstract protected function prepareRequests($subscribers, $payload = null);

    /**
     * Send data to provider.
     *
     * @param  array $subscribers
     * @param  \Closure $success
     * @param  \Closure $fail
     */
    protected function flush(array $subscribers, \Closure $success = null, \Closure $fail = null)
    {
        if (is_null($success)) {
            $success = function ($response, $index) {};
        }
        if (is_null($fail)) {
            $fail = function ($reason, $index) {};
        }

        $pool = new Pool(static::$browser, $this->prepareRequests($subscribers), [
            'concurrency' => $this->config['concurrent_requests'],
            'fulfilled'   => $success,
            'rejected'    => $fail,
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();
    }
}
