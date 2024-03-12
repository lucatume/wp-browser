<?php


namespace Unit\lucatume\WPBrowser\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Suite;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Events\Dispatcher;
use lucatume\WPBrowser\Extension\EventDispatcherBridge;
use lucatume\WPBrowser\Traits\UopzFunctions;
use PHPUnit\Framework\Assert;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventDispatcherBridgeTest extends Unit
{
    use UopzFunctions;

    /**
     * It should throw if event dispatcher cannot be found in trace
     *
     * @test
     */
    public function should_throw_if_event_dispatcher_cannot_be_found_in_trace(): void
    {
        $mockTrace = [];
        $this->setFunctionReturn('debug_backtrace', $mockTrace);

        $eventDispatcherBridge = new EventDispatcherBridge([], []);

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('Could not find the application event dispatcher.');

        $suite = PHP_VERSION >= 8.0 ? null : new Suite;
        $eventDispatcherBridge->onModuleInit(new SuiteEvent($suite));
    }

    /**
     * It should set global event dispatcher to application one
     *
     * @test
     */
    public function should_set_global_event_dispatcher_to_application_one(): void
    {
        $eventDispatcher = new EventDispatcher();
        $mockTrace = [
            ['object' => $eventDispatcher]
        ];
        $this->setFunctionReturn('debug_backtrace', $mockTrace);
        $this->setMethodReturn(Dispatcher::class, 'getEventDispatcher', null);
        $this->setMethodReturn(Dispatcher::class,
            'setEventDispatcher',
            function (EventDispatcherInterface $setEventDispatcher) use (
                $eventDispatcher
            ) {
                Assert::assertSame($eventDispatcher, $setEventDispatcher);
            });

        $eventDispatcherBridge = new EventDispatcherBridge([], []);
        $suite = PHP_VERSION >= 8.0 ? null : new Suite;
        $eventDispatcherBridge->onSuiteInit(new SuiteEvent($suite));
    }

    /**
     * It should immediately call previous event dispatcher listeners on this event
     *
     * @test
     */
    public function should_immediately_call_previous_event_dispatcher_listeners_on_this_event(): void
    {
        $eventDispatcher = new EventDispatcher();
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
        $mockTrace = [
            ['object' => $eventDispatcher]
        ];
        $this->setFunctionReturn('debug_backtrace', $mockTrace);
        $this->setMethodReturn(Dispatcher::class, 'getEventDispatcher', $previousEventDispatcher);
        $this->setMethodReturn(Dispatcher::class, 'setEventDispatcher', null);

        $eventDispatcherBridge = new EventDispatcherBridge([], []);
        $suite = PHP_VERSION >= 8.0 ? null : new Suite;
        $eventDispatcherBridge->onSuiteBefore(new SuiteEvent($suite));

        $this->assertEquals(2, $calls);
    }

    /**
     * It should correctly handle the case where the previous event dispatcher is null
     *
     * @test
     */
    public function should_correctly_handle_the_case_where_the_previous_event_dispatcher_is_null(): void
    {
        $eventDispatcher = new EventDispatcher();
        $mockTrace = [
            ['object' => $eventDispatcher]
        ];
        $this->setFunctionReturn('debug_backtrace', $mockTrace);
        $this->setMethodReturn(Dispatcher::class, 'getEventDispatcher', null);
        $this->setMethodReturn(Dispatcher::class,
            'setEventDispatcher',
            function ($eventDispatcher) use (&$setEventDispatcher) {
                $setEventDispatcher = $eventDispatcher;
            } , true);

        $eventDispatcherBridge = new EventDispatcherBridge([], []);
        $suite = PHP_VERSION >= 8.0 ? null : new Suite;
        $eventDispatcherBridge->onSuiteBefore(new SuiteEvent($suite));

        $this->assertSame($eventDispatcher, $setEventDispatcher);
    }
}
