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
    abstract public function getPayloadForSubscriber(Subscriber $subscriber);

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
            'icon' => $this->makeIcon($notification),
            'tag' => $this->makeTag($notification),
        ];
    }

    /**
     * Make onclick url redirect.
     *
     * @return string
     */
    public function makeClickUrl($notification)
    {
        if (empty($this->project->config['payload']['url'])) {
            throw new \RuntimeException('Payload url is invalid');
        }

        $url = $this->project->config['payload']['url'];

        return ! $this->isAbsoluteUrl($url)
            ? $this->project->baseUrl . '/' . $url
            : $url;
    }

    /**
     * Make notification icon.
     *
     * @return string
     */
    protected function makeIcon($notification)
    {
        if (empty($this->project->config['icon_path'])) {
            return null;
        }

        $icon = $this->project->config['icon_path'];

        return ! $this->isAbsoluteUrl($icon)
            ? $this->project->baseUrl . '/' . $icon
            : $icon;
    }

    /**
     * Make notification tag.
     *
     * @return string
     */
    protected function makeTag($notification)
    {
        return md5($this->project->baseUrl);
    }

    /**
     * Check if url is absolute
     *
     * @param  string $url
     * @return bool
     */
    protected function isAbsoluteUrl($url)
    {
        return (bool) preg_match('/^https:\/\//', $url);
    }
}
