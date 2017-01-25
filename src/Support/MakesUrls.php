<?php

namespace Notimatica\Driver\Support;

use Notimatica\Driver\Contracts\Notification;
use Notimatica\Driver\Contracts\Project;

trait MakesUrls
{
    /**
     * @var Project
     */
    protected $project;

    /**
     * Make onclick url redirect.
     *
     * @param  Notification $notification
     * @return string
     */
    public function makeClickUrl(Notification $notification = null)
    {
        if (! is_null($notification)) {
            $url = $notification->getUrl();
        }

        if (empty($url)) {
            $url = $this->getProject()->getBaseUrl();
        }

        return $this->formatUrlFrom($url);
    }

    /**
     * Make notification icon.
     *
     * @param  Notification $notification
     * @return string
     */
    public function makeIconUrl(Notification $notification = null)
    {
        $icon = $this->getProject()->getIcon();

        return $this->formatUrlFrom($icon);
    }

    /**
     * If url is absolute, return it. If not, merge with project's base url.
     *
     * @param  string $url
     * @return string
     */
    protected function formatUrlFrom($url)
    {
        return ! $this->isAbsoluteUrl($url)
            ? trim($this->getProject()->getBaseUrl(), '/') . '/' . ltrim($url, '/')
            : $url;
    }

    /**
     * Check if url is absolute
     *
     * @param  string $url
     * @return bool
     */
    protected function isAbsoluteUrl($url)
    {
        return (bool) preg_match('/^https?:\/\//', $url);
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
     * @param Project $project
     */
    public function setProject(Project $project)
    {
        $this->project = $project;
    }
}