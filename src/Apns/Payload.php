<?php namespace Notimatica\Driver\Apns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use Notimatica\Driver\Contracts\Notification;

class Payload implements \JsonSerializable, Arrayable
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
     * Specify data which should be serialized to JSON
     */
    function jsonSerialize()
    {
        return $this->toArray();
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
                    'title' => Str::limit($this->notification->getTitle(), static::TITLE_LENGTH - 3),
                    'body' => Str::limit($this->notification->getBody(), static::BODY_LENGTH - 3),
                ],
                'url-args' => [
                    $this->notification->getUuid(),
                ],
            ],
        ];
    }
}