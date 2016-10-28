<?php

namespace Notimatica\Driver\Support;

use League\Event\Emitter;
use League\Event\EventInterface;
use League\Event\ListenerInterface;
use Notimatica\Driver\StatisticsStoragesFactory;

trait EventsEmitter
{
    /**
     * @var Emitter
     */
    public static $events;

    /**
     * Boot events emitter.
     */
    public function bootEvents()
    {
        static::$events = new Emitter();

        $this->bootListeners();
    }

    /**
     * Boot event listeners.
     */
    protected function bootListeners()
    {
        $storage = $this->project->config['statistics']['storage'];
        $statisticsStorage = (new StatisticsStoragesFactory())->make($storage);

        return static::$events->useListenerProvider($statisticsStorage);
    }

    /**
     * Emit event.
     *
     * @return \League\Event\EventInterface|string
     */
    public static function emit()
    {
        return call_user_func_array([static::$events, 'emit'], func_get_args());
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
        return static::$events->addListener($event, $listener);
    }

    /**
     * Remove listeners.
     *
     * @param $event
     */
    public static function off($event)
    {
        static::$events->removeAllListeners($event);
    }
}
