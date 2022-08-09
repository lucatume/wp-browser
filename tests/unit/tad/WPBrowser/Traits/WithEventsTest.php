<?php

use Codeception\Application;
use Codeception\Codecept;
use Codeception\Command\Run;
use lucatume\WPBrowser\Events\EventDispatcherAdapter;
use lucatume\WPBrowser\Traits\WithEvents;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use function lucatume\WPBrowser\setPrivateProperties;

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
        EventDispatcherAdapter::resetSharedInstance();
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

        $this->assertInstanceOf(EventDispatcherAdapter::class, $eventDispatcher);
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
        $mockSymfonyEventDispatcher = $this->makeEmpty(SymfonyEventDispatcher::class);
        $codecept = $this->makeEmpty(Codecept::class, [
            'getDispatcher' => $mockSymfonyEventDispatcher
        ]);
        setPrivateProperties($runningCommand, [ 'codecept' => $codecept ]);
        setPrivateProperties($app, [ 'runningCommand' => $runningCommand ]);

        $eventDispatcher = $this->getEventDispatcher();

        $this->assertInstanceOf(EventDispatcherAdapter::class, $eventDispatcher);
        $this->assertSame($mockSymfonyEventDispatcher, $eventDispatcher->getOriginalEventDispatcher());
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
}
