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
     * Emit event.
     *
     * @param  string|EventInterface $event
     * @return \League\Event\EventInterface|string
     */
    public static function emitEvent($event)
    {
        return static::$events->emit($event);
    }

    /**
     * Emit event.
     *
     * @param  string $event
     * @param  ListenerInterface|callable $listener
     * @return EventInterface|string
     */
    public static function listenToEvent($event, $listener)
    {
        return static::$events->addListener($event, $listener);
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
}
