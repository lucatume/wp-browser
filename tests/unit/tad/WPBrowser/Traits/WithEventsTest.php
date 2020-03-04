<?php

use Codeception\Application;
use Codeception\Codecept;
use Codeception\Command\Run;
use Codeception\Exception\TestRuntimeException;
use Codeception\Util\ReflectionPropertyAccessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\EventDispatcher\EventDispatcher;
use tad\WPBrowser\Traits\WithEvents;

class WithEventsTest extends \Codeception\Test\Unit
{
    use WithEvents;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \Codeception\Application
     */
    protected $appBackup;

    protected function _before()
    {
        static::$dispatcher = null;
    }

    protected function _after()
    {
        if ($this->appBackup !== null) {
            global $app;
            $app = $this->appBackup;
            unset($this->appBackup);
        }
    }

    /**
     * It should create and return a shared event dispatcher instance if running command not found
     *
     * @test
     */
    public function should_create_and_return_a_shared_event_dispatcher_instance_if_running_command_not_found()
    {
        global $app;
        $this->appBackup = $app;
        $app = null;

        $eventDispatcher = $this->getEventDispatcher();
        $appDispatcher = $this->getAppEventDispatcher();

        $this->assertInstanceOf(EventDispatcher::class, $eventDispatcher);
        $this->assertNull($appDispatcher);
    }

    /**
     * It should return the global app event dispatcher if defined
     *
     * @test
     */
    public function should_return_the_global_app_event_dispatcher_if_defined()
    {
        global $app;
        $this->appBackup = $app;
        $app = $this->makeEmpty(Application::class);
        $runningCommand = $this->makeEmpty(Run::class);
        $theDispatcher = new EventDispatcher();
        $codecept = $this->makeEmpty(Codecept::class, [
            'getDispatcher' => $theDispatcher
        ]);
        $props = new ReflectionPropertyAccessor();
        $props->setProperties($runningCommand, ['codecept' => $codecept]);
        $props->setProperties($app, ['runningCommand' => $runningCommand]);

        $eventDispatcher = $this->getEventDispatcher();
        $appDispatcher = $this->getAppEventDispatcher();

        $this->assertInstanceOf(EventDispatcher::class, $eventDispatcher);
        $this->assertInstanceOf(EventDispatcher::class, $appDispatcher);
        $this->assertSame($eventDispatcher, $appDispatcher);
        $this->assertSame($eventDispatcher, $theDispatcher);
    }

    /**
     * It should throw if global app is not correct type
     *
     * @test
     */
    public function should_throw_if_global_app_is_not_correct_type()
    {
        global $app;
        $this->appBackup = $app;
        $app = new stdClass();

        $eventDispatcher = $this->getEventDispatcher();

        $this->assertInstanceOf(EventDispatcher::class, $eventDispatcher);
        $this->assertNull($this->getAppEventDispatcher());
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
        $app = $this->makeEmpty(Application::class);
        $runningCommand = new stdClass();
        $props = new ReflectionPropertyAccessor();
        $props->setProperties($app, ['runningCommand' => $runningCommand]);

        $this->expectException(TestRuntimeException::class);

        $this->getEventDispatcher();

        $this->expectException(TestRuntimeException::class);

        $this->getAppEventDispatcher();
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
        $app = $this->makeEmpty(Application::class);
        $runningCommand = $this->makeEmpty(Run::class);
        $codecept = new stdClass();
        $props = new ReflectionPropertyAccessor();
        $props->setProperties($runningCommand, ['codecept' => $codecept]);
        $props->setProperties($app, ['runningCommand' => $runningCommand]);

        $this->expectException(TestRuntimeException::class);

        $this->getEventDispatcher();

        $this->expectException(TestRuntimeException::class);

        $this->getAppEventDispatcher();
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
        $app = $this->makeEmpty(Application::class);
        $runningCommand = $this->makeEmpty(Run::class);
        $codecept = $this->makeEmpty(Codecept::class, [
            'getDispatcher' => new stdClass()
        ]);
        $props = new ReflectionPropertyAccessor();
        $props->setProperties($runningCommand, ['codecept' => $codecept]);
        $props->setProperties($app, ['runningCommand' => $runningCommand]);

        $this->expectException(TestRuntimeException::class);

        $this->getEventDispatcher();

        $this->expectException(TestRuntimeException::class);

        $this->getAppEventDispatcher();
    }

    /**
     * It should allow adding actions and dispatching them
     *
     * @test
     */
    public function should_allow_adding_actions_and_dispatching_them()
    {
        global $app;
        $this->appBackup = $app;
        $calledTimes = 0;
        $this->addAction('test_event', static function () use (&$calledTimes) {
            $calledTimes++;
        });

        $dispatcher = $this->getEventDispatcher();
        $dispatcher->dispatch('test_event');
        $dispatcher->dispatch('test_event');
        $dispatcher->dispatch('test_event');

        $this->assertEquals(3, $calledTimes);
    }

    /**
     * It should throw if expected prop not set
     *
     * @test
     */
    public function should_throw_if_expected_prop_not_set()
    {
        global $app;
        $this->appBackup = $app;
        $app = $this->makeEmpty(Application::class);
        $runningCommand = $this->makeEmpty(Run::class);
        $theDispatcher = new EventDispatcher();
        $codecept = $this->makeEmpty(Codecept::class, [
            'getDispatcher' => $theDispatcher
        ]);
        $props = new ReflectionPropertyAccessor();
        $props->setProperties($runningCommand, ['codecept' => $codecept]);

        $this->expectException(TestRuntimeException::class);

        $this->getEventDispatcher();

        $this->expectException(TestRuntimeException::class);

        $this->getAppEventDispatcher();
    }
}
