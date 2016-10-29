<?php

namespace Notimatica\Driver\Apns;

use Notimatica\Driver\Contracts\Notification;

class Payload implements \JsonSerializable
{
    const TITLE_LENGTH = 40;
    const BODY_LENGTH = 90;

    /**
     * @var Notification
     */
    private $notification;

    /**
     * Construct.
     *
     * @param Notification $notification
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'aps' => [
                'alert' => [
                    'title' => $this->limitString($this->notification->getTitle(), static::TITLE_LENGTH - 3),
                    'body' => $this->limitString($this->notification->getBody(), static::BODY_LENGTH - 3),
                ],
                'url-args' => [
                    $this->notification->getId(),
                ],
            ],
        ];
    }

    /**
     * Specify data which should be serialized to JSON.
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Limit string size.
     * Realization: Laravel.
     *
     * @param  string $value
     * @param  int $limit
     * @param  string $end
     * @return string
     */
    protected function limitString($value, $limit = 10, $end = '...')
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }
}
