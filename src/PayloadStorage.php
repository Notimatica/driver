<?php

namespace Notimatica\Driver;

use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Subscriber;

abstract class PayloadStorage
{
    /**
     * @var Project
     */
    private $project;

    /**
     * Create a new PayloadStorage.
     *
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Search and return payload for subscriber.
     *
     * @param  Subscriber $subscriber
     * @return Notification
     */
    abstract public function getPayload(Subscriber $subscriber);

    /**
     * Save payload for endpoint.
     *
     * @param  Notification $notification
     * @param  Subscriber $subscriber
     * @param  int $lifetime
     */
    abstract public function assignPayloadToSubscriber(
        Notification $notification,
        Subscriber $subscriber,
        $lifetime = 86400
    );

    /**
     * @param  Notification $notification
     * @return array
     */
    public function makePayloadFromNotification(Notification $notification)
    {
        return [
            'id' => $notification->getId(),
            'title' => $notification->getTitle(),
            'body' => $notification->getBody(),
            'url' => $this->makeClickUrl($notification),
            'icon' => $this,
            'tag' => $this->makeTag(),
        ];
    }

    /**
     * Make onclick url redirect.
     *
     * @return string
     */
    public function makeClickUrl()
    {
        if (empty($this->project->config['payload']['url'])) {
            throw new \RuntimeException('Payload url is invalid');
        }

        $url = $this->project->config['payload']['url'];

        return starts_with('https://', $url)
            ? $url
            : $this->project->baseUrl . '/' . $this->project->config['payload']['url'];
    }

    /**
     * Make notification tag.
     *
     * @return string
     */
    private function makeTag()
    {
        return md5($this->project->baseUrl);
    }
}
