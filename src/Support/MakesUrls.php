<?php

namespace Notimatica\Driver\Support;

use Notimatica\Driver\Contracts\Notification;

trait MakesUrls
{
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
    protected function makeIcon(Notification $notification = null)
    {
        $config = $this->project->getConfig();

        if (empty($config['icon_path'])) {
            return null;
        }

        return $this->formatUrlFromConfig($config['icon_path']);
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
            ? $this->project->getBaseUrl() . '/' . $url
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
        return (bool) preg_match('/^https:\/\//', $url);
    }
}