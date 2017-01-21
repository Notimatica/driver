<?php

namespace Notimatica\Driver\Support;

use League\Event\Emitter;
use League\Event\EventInterface;
use League\Event\ListenerInterface;

trait EventsEmitter
{
    /**
     * @var Emitter
     */
    public static $emitter;

    /**
     * Boot events emitter.
     */
    public function bootEvents()
    {
        static::$emitter = new Emitter();
    }

    /**
     * Emit event.
     *
     * @return \League\Event\EventInterface|string
     */
    public static function emit()
    {
        return call_user_func_array([static::$emitter, 'emit'], func_get_args());
    }

    /**
     * Emit event.
     *
     * @param  string $event
     * @param  ListenerInterface|callable $listener
     * @return EventInterface|string
     */
    public static function on($event, $listener)
    {
        return static::$emitter->addListener($event, $listener);
    }

    /**
     * Remove listeners.
     *
     * @param $event
     */
    public static function off($event)
    {
        static::$emitter->removeAllListeners($event);
    }

    /**
     * @param Emitter $events
     */
    public static function setEmitter(Emitter $events)
    {
        self::$emitter = $events;
    }
}
