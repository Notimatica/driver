<?php

namespace Notimatica\Driver\Support;

trait HasHttpsImage
{
    /**
     * Ensure image is https.
     *
     * @param  string $image
     * @return string|null
     */
    public function ensureHttps($image)
    {
        return ! empty($image) && $this->needToReplace($image)
            ? 'https://images.weserv.nl/?url=' . str_replace('http://', '', $image)
            : $image;
    }

    /**
     * Check if url needs to be replaced.
     *
     * @param  string $image
     * @return bool
     */
    protected function needToReplace($image)
    {
        $parsed = parse_url($image);

        return ! ($parsed['scheme'] == 'https' ||
                  $parsed['scheme'] == 'ssl'   ||
                  preg_match('/192\.168|localhost|images\.weserv\.nl/', $parsed['host']));
    }

}
