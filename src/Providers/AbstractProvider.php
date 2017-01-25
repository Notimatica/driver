<?php

namespace Notimatica\Driver\Providers;

use League\Event\EmitterInterface;
use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Project;
use Notimatica\Driver\Contracts\Subscriber;

abstract class AbstractProvider
{
    const NAME = null;

    /**
     * @var array
     */
    protected $config = [];
    /**
     * @var array
     */
    protected $emitter = [];
    /**
     * @var Project
     */
    protected $project;

    /**
     * @var EmitterInterface
     */
    protected static $dispatcher;

    /**
     * Create a new Provider.
     *
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->config = $this->project->getProviderConfig(static::NAME);
    }

    /**
     * If provider is enabled.
     *
     * @return bool
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

    /**
     * Events dispatcher setter.
     *
     * @param EmitterInterface $dispatcher
     */
    public static function setEventDispatcher(EmitterInterface $dispatcher)
    {
        static::$dispatcher = $dispatcher;
    }
}
