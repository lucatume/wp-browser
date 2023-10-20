<?php

namespace lucatume\WPBrowser\Events;


use Codeception\Events;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Property;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DispatcherTest extends Unit
{
    use UopzFunctions;

    private \Symfony\Component\EventDispatcher\EventDispatcherInterface $backupGlobalDispatcher;

    public function _before(): void
    {
        $this->backupGlobalDispatcher = Dispatcher::getEventDispatcher();
    }

    public function _after(): void
    {
        Dispatcher::setEventDispatcher($this->backupGlobalDispatcher);
        Dispatcher::setEventDispatcher(null);
    }

    /**
     * It should allow listening to and dispatching events
     *
     * @test
     */
    public
    function should_allow_listening_to_and_dispatching_events()
    {
        $callStack = [];
        Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'second';
        });
        $removeThird = Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'third';
        }, -10);
        $removeFirst = Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'first';
        }, 100);

        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));

        $this->assertEquals([
            'first',
            'second',
            'third',
        ], $callStack);
        $callStack = [];

        Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'new first';
        }, 200);

        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));

        $this->assertEquals([
            'new first',
            'first',
            'second',
            'third',
        ], $callStack);
        $callStack = [];

        $removeThird();
        $removeFirst();

        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));

        $this->assertEquals([
            'new first',
            'second',
        ], $callStack);
    }

    /**
     * It should return a purposely built Dispatcher when the Codeception instance is not available
     *
     * @test
     */
    public
    function should_return_a_purposely_built_dispatcher_when_the_codeception_instance_is_not_available(): void
    {
        $previousDispatcher = Dispatcher::getEventDispatcher();

        Dispatcher::setEventDispatcher();

        $eventDispatcherInterface = interface_exists(\Psr\EventDispatcher\EventDispatcherInterface::class) ?
            EventDispatcherInterface::class
            : \Symfony\Component\EventDispatcher\EventDispatcherInterface::class;

        $this->assertInstanceOf($eventDispatcherInterface, Dispatcher::getEventDispatcher());
        $this->assertNotSame($previousDispatcher, Dispatcher::getEventDispatcher());

        $callStack = [];
        Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'second';
        });
        $removeThird = Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'third';
        }, -10);
        $removeFirst = Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'first';
        }, 100);

        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));

        $this->assertEquals([
            'first',
            'second',
            'third',
        ], $callStack);
        $callStack = [];

        Dispatcher::addListener('TEST_EVENT', static function () use (&$callStack) {
            $callStack[] = 'new first';
        }, 200);

        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));

        $this->assertEquals([
            'new first',
            'first',
            'second',
            'third',
        ], $callStack);
        $callStack = [];

        $removeThird();
        $removeFirst();

        $this->assertInstanceOf(Event::class, Dispatcher::dispatch('TEST_EVENT'));

        $this->assertEquals([
            'new first',
            'second',
        ], $callStack);
    }

    /**
     * It should port over listeners and priorities from previous dispatcher when setting new one
     *
     * @test
     */
    public
    function should_port_over_listeners_and_priorities_from_previous_dispatcher_when_setting_new_one(): void
    {
        $previousEventDispatcher = new EventDispatcher();
        $calls = 0;
        $callbackOne = function () use (&$calls) {
            return $calls++;
        };
        $previousEventDispatcher->addListener(Events::SUITE_BEFORE, $callbackOne, 89);
        $callbackTwo = function () use (&$calls) {
            return $calls++;
        };
        $previousEventDispatcher->addListener(Events::SUITE_BEFORE, $callbackTwo, 23);

        Dispatcher::setEventDispatcher($previousEventDispatcher);

        $newEventDispatcher = new EventDispatcher();

        Dispatcher::setEventDispatcher($newEventDispatcher);

        $this->assertEquals($newEventDispatcher->getListeners(), $previousEventDispatcher->getListeners());
    }
}
