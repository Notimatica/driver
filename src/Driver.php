<?php

namespace Notimatica\Driver;

use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Providers\AbstractProvider;
use Notimatica\Driver\Support\EventsEmitter;

class Driver
{
    use EventsEmitter;

    /**
     * @var Project
     */
    protected $project;
    /**
     * @var Notification
     */
    protected $notification;
    /**
     * @var array
     */
    protected $subscribers;
    /**
     * @var PayloadStorage
     */
    protected $payloadStorage;

    /**
     * Create a new Driver.
     *
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;

        $this->boot();
    }

    /**
     * Boot driver.
     */
    public function boot()
    {
        $this->bootEvents();
    }

    /**
     * Send notification.
     *
     * @param  Notification $notification
     * @return $this
     */
    public function send(Notification $notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Endpoints to send to.
     *
     * @param  array $subscribers
     * @return $this
     */
    public function to(array $subscribers)
    {
        $this->subscribers = $subscribers;

        return $this;
    }

    /**
     * Send notification.
     */
    public function flush()
    {
        $this->validate();

        $partials = $this->splitSubscribers();

        foreach ($partials as $provider => $subscribers) {
            try {
                $this->provider($provider)->send($this->notification, $subscribers);
            } catch (\RuntimeException $e) {
                static::emit('flush.exception', $e);
            }
        }
    }

    /**
     * Generate connection package.
     *
     * @param string $provider
     * @param array $extra
     */
    public function sendPackage($provider, $extra = [])
    {
        return $this->provider($provider)->connectionPackage($extra);
    }

    /**
     * Cast provider.
     *
     * @param  string $name
     * @return AbstractProvider
     * @throws \RuntimeException
     */
    public function provider($name)
    {
        return $this->project->getProvider($name);
    }

    /**
     * Prepare notifications.
     * Split subscribers by their providers and prepare payload.
     */
    protected function splitSubscribers()
    {
        $partials = [];

        /** @var Subscriber $subscriber */
        foreach ($this->subscribers as $subscriber) {
            $provider = $subscriber->getProvider();

            if (! $this->project->providerConnected($provider)) {
                continue;
            }

            if (! isset($partials[$provider])) {
                $partials[$provider] = [];
            }

            $partials[$provider][] = $subscriber;

            if ($this->payloadStorage) {
                $this->payloadStorage->assignNotificationToSubscriber($subscriber, $this->notification);
            }
        }

        return $partials;
    }

    /**
     * Validate data.
     *
     * @throws \RuntimeException
     */
    protected function validate()
    {
        if (is_null($this->notification)) {
            throw new \RuntimeException("Notification wasn't set.");
        }

        if (is_null($this->subscribers)) {
            throw new \RuntimeException("No subscribers set.");
        }
    }

    /**
     * Set project manually.
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
     * Get project instance.
     *
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
