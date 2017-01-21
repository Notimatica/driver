<?php

namespace Notimatica\Driver\Support;

use League\Event\Emitter;
use League\Event\EmitterInterface;
use League\Event\EventInterface;
use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerInterface;

trait EventsDispatcher
{
    /**
     * @var EmitterInterface
     */
    protected $dispatcher;

    /**
     * Events dispatcher
     *
     * @return EmitterInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param EmitterInterface $dispatcher
     */
    public function setDispatcher(EmitterInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
}
