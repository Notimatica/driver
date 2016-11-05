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
     * @var Notification
     */
    protected $notification;
    /**
     * @var array
     */
    protected $subscribers;
    /**
     * @var PayloadStorageContract
     */
    protected $payloadStorage;
    /**
     * @var Statistics
     */
    protected $statisticsStorage;
    /**
     * @var NotificationRepository
     */
    private $notificationRepository;
    /**
     * @var SubscriberRepository
     */
    private $subscriberRepository;

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
     * Set project manually.
     *
     * @param  Project $project
     * @return $this
     */
    public function from(Project $project)
    {
        $this->project = $project;

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
                $this->provider($provider)->send($this->notification, $subscribers);
            } catch (\RuntimeException $e) {
                static::emit('flush.exception', $e);
            }
        }
    }

    /**
     * Get payload for the subscriber.
     *
     * @param  string $subscriberToken
     * @return Notification
     */
    public function retrievePayload($subscriberToken)
    {
        if (empty($subscriberToken)) throw new \RuntimeException('Empty subscriber token.');

        $subscriber     = $this->subscriberRepository->findByToken($subscriberToken);
        $payload        = $this->payloadStorage->getPayloadForSubscriber($subscriber);
        $notification   = $this->notificationRepository->find($payload['id']);

        static::emit(new NotificationDelivered($notification));

        return $payload;
    }

    /**
     * Process notification click.
     *
     * @param  int|string $notificationId
     * @return string
     */
    public function processClicked($notificationId)
    {
        if (empty($notificationId)) throw new \RuntimeException('Empty notification id.');

        $notification = $this->notificationRepository->find($notificationId);

        static::emit(new NotificationClicked($notification));

        return $notification->getUrl();
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
     *
     * @param  Subscriber[] $subscribers
     * @return array
     */
    protected function splitSubscribers(array $subscribers)
    {
        $partials = [];

        foreach ($subscribers as $subscriber) {
            $provider = $subscriber->getProvider();

            if (! $this->project->providerConnected($provider)) {
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
                    $this->project->getConfig()['payload']['subscriber_lifetime']
                );
            }
        }

        return $partials;
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

    /**
     * Boot event listeners.
     */
    protected function bootListeners()
    {
        if (! is_null($this->statisticsStorage)) {
            static::$events->useListenerProvider($this->statisticsStorage);
        }
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
}
