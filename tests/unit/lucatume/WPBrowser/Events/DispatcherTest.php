<?php

namespace lucatume\WPBrowser\Events;


use Codeception\Test\Unit;
use Psr\EventDispatcher\EventDispatcherInterface;

class DispatcherTest extends Unit
{

    /**
     * It should allow listening to and dispatching events
     *
     * @test
     */
    public function should_allow_listening_to_and_dispatching_events()
    {
        $calledTimes = 0;
        Dispatcher::addListener('TEST_EVENT',
            function (Event $event, string $name, EventDispatcherInterface $eventDispatcher) use (&$calledTimes) {
                static $lastDispatchedEvent = null;
                $this->assertNotSame($lastDispatchedEvent, $event);
                $calledTimes++;
            });

        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));
        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));
        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));
    }
}
