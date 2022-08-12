<?php


namespace lucatume\WPBrowser\Events;

use Codeception\Application;
use Codeception\Codecept;
use Codeception\Command\Run;
use Codeception\Exception\TestRuntimeException;
use lucatume\WPBrowser\StubProphecy\Arg;
use lucatume\WPBrowser\Traits\WithStubProphecy;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use function lucatume\WPBrowser\setPrivateProperties;

class EventDispatcherAdapterTest extends \Codeception\Test\Unit
{
    use WithStubProphecy;

    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var \Closure
     */
    protected $listener;
    /**
     * @var EventDispatcherAdapter
     */
    protected $adapter;

    protected function _before()
    {
        EventDispatcherAdapter::resetSharedInstance();
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
        $mockSymfonyEventDispatcher = $this->stubProphecy(SymfonyEventDispatcher::class);
        $mockSymfonyEventDispatcher->dispatch(Arg::type(WpbrowserEvent::class), 'test/event')
                                   ->shouldBeCalledOnce()
                                   ->willReturn(new WpbrowserEvent('test'));
        $mockSymfonyEventDispatcher->addListener('test/event', $listener, 23)
                                   ->shouldBeCalledOnce();

        $adapter = new EventDispatcherAdapter($mockSymfonyEventDispatcher->reveal());

        if (! EventDispatcherAdapter::dispatchWithObject()) {
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
        $mockSymfonyEventDispatcher = $this->stubProphecy(SymfonyEventDispatcher::class);
        $mockSymfonyEventDispatcher->dispatch('test/event', Arg::type(WpbrowserEvent::class))
                                   ->shouldBeCalledOnce()
        ->willReturn(new WpbrowserEvent('test'));
        $mockSymfonyEventDispatcher->addListener('test/event', $listener, 23)
                                   ->shouldBeCalledOnce();

        $adapter = new EventDispatcherAdapter($mockSymfonyEventDispatcher->reveal());

        if (EventDispatcherAdapter::dispatchWithObject()) {
            $this->markTestSkipped(
                'This should not run if the EventDispatcher::dispatch requires an object as first argument.'
            );
        }

        $adapter->addListener('test/event', $listener, 23);
        $adapter->dispatch('test/event', 'test-test-test', [ 'lorem' => 'dolor' ]);
    }

    /**
     * It should throw if expected prop not set
     *
     * @test
     */
    public function should_throw_if_expected_prop_not_set()
    {
        global $app;
        $this->appBackup            = $app;
        $app                        = $this->makeEmpty(Application::class);
        $runningCommand             = $this->makeEmpty(Run::class);
        $mockSymfonyEventDispatcher = $this->makeEmpty(SymfonyEventDispatcher::class);
        $codecept                   = $this->makeEmpty(Codecept::class, [
            'getDispatcher' => $mockSymfonyEventDispatcher
        ]);
        setPrivateProperties($runningCommand, [ 'codecept' => $codecept ]);

        $this->expectException(TestRuntimeException::class);

        EventDispatcherAdapter::getEventDispatcher();
    }

    /**
     * It should throw if event dispatcher is not correct type
     *
     * @test
     */
    public function should_throw_if_event_dispatcher_is_not_correct_type()
    {
        global $app;
        $this->appBackup = $app;
        $app             = $this->makeEmpty(Application::class);
        $runningCommand  = $this->makeEmpty(Run::class);
        $codecept        = $this->makeEmpty(Codecept::class, [
            'getDispatcher' => new \stdClass()
        ]);
        setPrivateProperties($runningCommand, [ 'codecept' => $codecept ]);
        setPrivateProperties($app, [ 'runningCommand' => $runningCommand ]);

        $this->expectException(TestRuntimeException::class);

        EventDispatcherAdapter::getEventDispatcher();
    }

    /**
     * It should throw if running command codecept is not correct type
     *
     * @test
     */
    public function should_throw_if_running_command_codecept_is_not_correct_type()
    {
        global $app;
        $this->appBackup = $app;
        $app             = $this->makeEmpty(Application::class);
        $runningCommand  = $this->makeEmpty(Run::class);
        $codecept        = new \stdClass();
        setPrivateProperties($runningCommand, [ 'codecept' => $codecept ]);
        setPrivateProperties($app, [ 'runningCommand' => $runningCommand ]);

        $this->expectException(TestRuntimeException::class);

        EventDispatcherAdapter::getEventDispatcher();
    }

    /**
     * It should throw if running app command is not correct type
     *
     * @test
     */
    public function should_throw_if_running_app_command_is_not_correct_type()
    {
        global $app;
        $this->appBackup = $app;
        $app             = $this->makeEmpty(Application::class);
        $runningCommand  = new \stdClass();
        setPrivateProperties($app, [ 'runningCommand' => $runningCommand ]);

        $this->expectException(TestRuntimeException::class);

        EventDispatcherAdapter::getEventDispatcher();
    }

    /**
     * It should return shared instance if global app is not correct type
     *
     * @test
     */
    public function should_return_shared_instance_if_global_app_is_not_correct_type()
    {
        global $app;
        $this->appBackup = $app;
        $app             = new \stdClass();

        $eventDispatcher = EventDispatcherAdapter::getEventDispatcher();

        $this->assertInstanceOf(EventDispatcherAdapter::class, $eventDispatcher);
    }
}
