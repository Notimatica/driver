<?php

namespace Notimatica\Driver;

use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\NotificationRepository;
use Notimatica\Driver\Contracts\Project;
use Notimatica\Driver\Contracts\Subscriber;
use Notimatica\Driver\Contracts\SubscriberRepository;
use Notimatica\Driver\Events\NotificationClicked;
use Notimatica\Driver\Events\NotificationDelivered;
use Notimatica\Driver\PayloadStorage as PayloadStorageContract;
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
     * @var AbstractProvider[]
     */
    protected $providers = [];
    /**
     * @var Notification
     */
    protected $notification;
    /**
     * @var array
     */
    protected $subscribers;
    /**
     * @var NotificationRepository
     */
    protected $notificationRepository;
    /**
     * @var SubscriberRepository
     */
    protected $subscriberRepository;
    /**
     * @var PayloadStorageContract
     */
    protected $payloadStorage;
    /**
     * @var Statistics
     */
    protected $statisticsStorage;

    /**
     * Create a new Driver.
     *
     * @param Project $project
     * @param NotificationRepository $notificationRepository
     * @param SubscriberRepository $subscriberRepository
     * @param PayloadStorage $payloadStorage
     * @param Statistics $statisticsStorage
     */
    public function __construct(
        Project $project,
        NotificationRepository $notificationRepository = null,
        SubscriberRepository $subscriberRepository = null,
        PayloadStorageContract $payloadStorage = null,
        Statistics $statisticsStorage = null
    )
    {
        $this->project = $project;
        $this->notificationRepository = $notificationRepository;
        $this->subscriberRepository = $subscriberRepository;
        $this->payloadStorage = $payloadStorage;
        $this->statisticsStorage = $statisticsStorage;

        $this->boot();
    }

    /**
     * Boot driver.
     */
    public function boot()
    {
        $this->bootEvents();
        $this->bootListeners();
    }

    /**
     * Boot event listeners.
     */
    protected function bootListeners()
    {
        if (! is_null($this->statisticsStorage)) {
            static::$emitter->useListenerProvider($this->statisticsStorage);
        }
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
    public function to(array $subscribers = [])
    {
        $this->subscribers = $subscribers;

        return $this;
    }

    /**
     * Make providers.
     *
     * @return AbstractProvider[]
     */
    public function getProviders()
    {
        if (is_null($this->providers)) {
            $this->buildProviders();
        }

        return $this->providers;
    }

    /**
     * Fetch connected provider.
     *
     * @param  string $name
     * @return AbstractProvider
     * @throws \InvalidArgumentException If provider isn't connected
     */
    public function getProvider($name)
    {
        if (! array_key_exists($name, $this->providers)) {
            $provider = $this->makeProvider($name);

            if (is_null($provider)) {
                throw new \InvalidArgumentException("Unsupported provider '{$name}'");
            }

            $this->providers[$name] = $provider;
        }

        return $this->providers[$name];
    }

    /**
     * Resolve provider.
     *
     * @param  string $name
     * @return AbstractProvider|null
     */
    protected function makeProvider($name)
    {
        $providersFactory = new ProvidersFactory($this->getProject());

        try {
            return $providersFactory->make($name);
        } catch (\LogicException $e) {
            return null;
        }
    }

    /**
     * Check if project has required provider.
     *
     * @param  string $name
     * @return bool
     */
    public function providerConnected($name)
    {
        return $this->makeProvider($name) !== null;
    }

    /**
     * Send notification.
     */
    public function flush()
    {
        if (is_null($this->notification)) {
            throw new \RuntimeException("Notification wasn't set.");
        }

        $partials = $this->splitSubscribers(
            is_null($this->subscribers)
                ? $this->subscriberRepository->all()
                : $this->subscribers
        );

        foreach ($partials as $provider => $subscribers) {
            try {
                $this->getProvider($provider)->send($this->notification, $subscribers);
            } catch (\RuntimeException $e) {
                static::emit('flush.exception', $e);
            }
        }
    }

    /**
     * Get payload for the subscriber.
     *
     * @param  Subscriber $subscriber
     * @return Notification
     */
    public function retrievePayload(Subscriber $subscriber)
    {
        $payload = $this->payloadStorage->getPayloadForSubscriber($subscriber);
        $notification = $this->notificationRepository->find($payload['id']);

        static::emit(new NotificationDelivered($notification));

        return $payload;
    }

    /**
     * Process notification click.
     *
     * @param  Notification $notification
     * @return string
     */
    public function processClicked(Notification $notification)
    {
        static::emit(new NotificationClicked($notification));

        return $notification->getUrl();
    }

    /**
     * Split subscribers by their providers and prepare payload.
     *
     * @param  Subscriber[] $subscribers
     * @return array
     */
    protected function splitSubscribers(array $subscribers)
    {
        $config = $this->getProject()->getConfig();
        $payloadLifetime = $config['payload']['subscriber_lifetime'];

        $partials = [];
        foreach ($subscribers as $subscriber) {
            $provider = $subscriber->getProvider();

            if (! $this->providerConnected($provider)) {
                continue;
            }

            if (! isset($partials[$provider])) {
                $partials[$provider] = [];
            }

            $partials[$provider][] = $subscriber;

            if ($this->payloadStorage) {
                $this->payloadStorage->assignPayloadToSubscriber(
                    $this->notification,
                    $subscriber,
                    $payloadLifetime
                );
            }
        }

        return $partials;
    }

    /**
     * Project getter.
     *
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Project setter.
     *
     * @param  Project $project
     */
    public function setProject(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Helper to return default package config.
     *
     * @return array
     */
    public static function getConfig()
    {
        return require(__DIR__ . '/../config/notimatica.php');
    }

    /**
     * NotificationRepository getter.
     *
     * @return NotificationRepository
     */
    public function getNotificationRepository()
    {
        return $this->notificationRepository;
    }

    /**
     * NotificationRepository setter.
     *
     * @param NotificationRepository $notificationRepository
     */
    public function setNotificationRepository(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * SubscriberRepository getter.
     *
     * @return SubscriberRepository
     */
    public function getSubscriberRepository()
    {
        return $this->subscriberRepository;
    }

    /**
     * SubscriberRepository setter.
     *
     * @param SubscriberRepository $subscriberRepository
     */
    public function setSubscriberRepository(SubscriberRepository $subscriberRepository)
    {
        $this->subscriberRepository = $subscriberRepository;
    }

    /**
     * PayloadStorage getter.
     *
     * @return PayloadStorage
     */
    public function getPayloadStorage()
    {
        return $this->payloadStorage;
    }

    /**
     * PayloadStorage setter.
     *
     * @param PayloadStorage $payloadStorage
     */
    public function setPayloadStorage(PayloadStorage $payloadStorage)
    {
        $this->payloadStorage = $payloadStorage;
    }

    /**
     * Statistics storage getter.
     *
     * @return Statistics
     */
    public function getStatisticsStorage()
    {
        return $this->statisticsStorage;
    }

    /**
     * Statistics storage setter.
     *
     * @param Statistics $statisticsStorage
     */
    public function setStatisticsStorage(Statistics $statisticsStorage)
    {
        $this->statisticsStorage = $statisticsStorage;
    }
}
