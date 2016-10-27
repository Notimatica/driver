<?php

namespace Notimatica\Driver\Support;

trait HasHttpsImage
{
    /**
     * Ensure image is https.
     *
     * @param  string $image
     * @return string
     */
    public function ensureHttps($image)
    {
        if (! $image) {
            return;
        }

        $parsed = parse_url($image);

        if ($parsed['scheme'] == 'https' ||
            $parsed['scheme'] == 'ssl'   ||
            preg_match('/192\.168|localhost|images\.weserv\.nl/', $parsed['host'])) {
            return $image;
        }

        return 'https://images.weserv.nl/?url=' . str_replace('http://', '', $image);
    }
}
