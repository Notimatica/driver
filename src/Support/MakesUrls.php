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
        $config = $this->project->getConfig();

        if (empty($config['payload']['click_url'])) {
            throw new \RuntimeException('Payload url is invalid');
        }

        return $this->formatUrlFromConfig($config['payload']['click_url']);
    }

    /**
     * Make notification icon.
     *
     * @param  Notification $notification
     * @return string
     */
    protected function makeIconUrl(Notification $notification = null)
    {
        $config = $this->project->getConfig();

        if (empty($config['icon'])) {
            return null;
        }

        return $this->formatUrlFromConfig($config['icon']);
    }

    /**
     * If url is absolute, return it. If not, merge with project's base url.
     *
     * @param  string $url
     * @return string
     */
    protected function formatUrlFromConfig($url)
    {
        return ! $this->isAbsoluteUrl($url)
            ? trim($this->project->getBaseUrl(), '/') . '/' . $url
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