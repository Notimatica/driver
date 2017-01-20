<?php

namespace Notimatica\Driver\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Contracts\Project;

abstract class AbstractProvider
{
    const NAME = null;

    /**
     * @var Project
     */
    protected $project;
    /**
     * @var array
     */
    protected $config = [];

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
        $this->project = $project;

        return $this;
    }

    /**
     * If provider is enabled.
     *
     * @return boolean
     */
    abstract public function isEnabled();

    /**
     * Send notification.
     *
     * @param  Notification $notification
     * @param  Subscriber[] $subscribers
     */
    abstract public function send(Notification $notification, array $subscribers);

    /**
     * Make request.
     *
     * @param  array $subscribers
     * @param  mixed $payload
     * @return \Generator
     */
    abstract protected function prepareRequests($subscribers, $payload = null);
}
