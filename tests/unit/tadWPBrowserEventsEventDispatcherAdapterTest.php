<?php

use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use tad\WPBrowser\Events\EventDispatcherAdapter;
use tad\WPBrowser\Events\WpbrowserEvent;

class tadWPBrowserEventsEventDispatcherAdapterTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var Closure
     */
    protected $listener;
    /**
     * @var EventDispatcherAdapter
     */
    protected $adapter;

    public function _before()
    {
    }

    /**
     * It should dispatch with object if dispatch method takes obj first argument
     *
     * @test
     */
    public function should_dispatch_with_object_if_dispatch_method_takes_obj_first_argument()
    {
        $listener                   = static function () {
        };
        $mockSymfonyEventDispatcher = $this->prophesize(SymfonyEventDispatcher::class);
        $mockSymfonyEventDispatcher->dispatch(Argument::type(WpbrowserEvent::class), 'test/event')
                                   ->shouldBeCalledOnce();
        $mockSymfonyEventDispatcher->addListener('test/event', $listener, 23)
                                   ->shouldBeCalledOnce();

        $adapter = new EventDispatcherAdapter($mockSymfonyEventDispatcher->reveal());

        if (! $adapter->dispatchWithObject()) {
            $this->markTestSkipped(
                'This should not run if the EventDispatcher::dispatch does not take an object as first argument.'
            );
        }

        $adapter->addListener('test/event', $listener, 23);
        $adapter->dispatch('test/event', 'test-test-test', [ 'lorem' => 'dolor' ]);
    }

    /**
     * It should dispatch with string if dispatch method does not require obj first argument
     *
     * @test
     */
    public function should_dispatch_with_string_if_dispatch_method_does_not_require_obj_first_argument()
    {
        $listener                   = static function () {
        };
        $mockSymfonyEventDispatcher = $this->prophesize(SymfonyEventDispatcher::class);
        $mockSymfonyEventDispatcher->dispatch('test/event', Argument::type(WpbrowserEvent::class))
                                   ->shouldBeCalledOnce();
        $mockSymfonyEventDispatcher->addListener('test/event', $listener, 23)
                                   ->shouldBeCalledOnce();

        $adapter = new EventDispatcherAdapter($mockSymfonyEventDispatcher->reveal());

        if ($adapter->dispatchWithObject()) {
            $this->markTestSkipped(
                'This should not run if the EventDispatcher::dispatch requires an object as first argument.'
            );
        }

        $adapter->addListener('test/event', $listener, 23);
        $adapter->dispatch('test/event', 'test-test-test', [ 'lorem' => 'dolor' ]);
    }
}
