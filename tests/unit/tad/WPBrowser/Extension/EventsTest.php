<?php

namespace tad\WPBrowser\Extension;

use tad\WPBrowser\Events\EventDispatcherAdapter;
use tad\WPBrowser\Events\WpbrowserEvent;

class EventsTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * It should return the correct list of subscribed events
     *
     * @test
     */
    public function should_return_the_correct_list_of_subscribed_events()
    {
        $actual = Events::getSubscribedEvents();

        $codeception_events = EventDispatcherAdapter::codeceptionEvents();
        $this->assertEquals($codeception_events, array_keys($actual));
    }

    /**
     * It should correctly build the event name
     *
     * @test
     */
    public function should_correctly_build_the_event_name()
    {
        $codeceptionEvents = EventDispatcherAdapter::codeceptionEvents();
        $firstEvent        = $codeceptionEvents[0];
        $dispatcher        = EventDispatcherAdapter::getEventDispatcher();
        $called            = false;
        $dispatcher->addListener(
            $firstEvent,
            function ($event, $eventName, $eventDispatcher) use ($firstEvent, &$called, $dispatcher) {
                $called = true;
                $this->assertInstanceOf(WpbrowserEvent::class, $event);
                $this->assertEquals($firstEvent, $eventName);
                $this->assertSame($dispatcher->getOriginalEventDispatcher(), $eventDispatcher);
            }
        );
        EventDispatcherAdapter::getEventDispatcher()->dispatch($firstEvent, 'test');

        $this->assertTrue($called);
    }

    /**
     * It should correctly register for all Codeception events
     *
     * @test
     */
    public function should_correctly_register_for_all_codeception_events()
    {
        $this->assertEquals(
            EventDispatcherAdapter::codeceptionEvents(),
            array_keys(Events::getSubscribedEvents())
        );
    }

    protected function _before()
    {
        EventDispatcherAdapter::resetSharedInstance();
    }

    protected function _after()
    {
    }
}
