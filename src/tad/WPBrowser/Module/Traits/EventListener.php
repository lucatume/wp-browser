<?php
/**
 * Provides methods to interact with Codeception `run` command event dispatch stack.
 *
 * @package tad\WPBrowser\Module\Traits
 */

namespace tad\WPBrowser\Module\Traits;

use Codeception\Application;
use Codeception\Exception\ModuleException;
use Codeception\Util\ReflectionHelper;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Trait EventListener
 *
 * @package tad\WPBrowser\Module\Traits
 * @property \Codeception\Lib\ModuleContainer $moduleContainer
 */
trait EventListener
{

    /**
     * The `run` command event dispatcher.
     *
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * Adds a callback to be performed on a global runner event..
     *
     * @param  string    $event     The event to run the callback on.
     * @param  callable  $callback  The callback to run on the event.
     * @param  int       $priority  The priority that will be assigned to the callback in the context of the event.
     *
     * @throws \Codeception\Exception\ModuleException
     */
    protected function addAction($event, $callback, $priority = 0)
    {
        $this->getEventDispatcher()->addListener($event, $callback, $priority);
    }

    /**
     * Returns the instance of the event dispatcher used by the `codecept run` command instance.
     *
     * @return EventDispatcher The event dispatcher instance used by the `run` command.
     *
     * @throws ModuleException If the global application instance is not a Codeception\Application instance; if the
     *                         `run` command dispatcher property cannot be accessed or is not an `EventDispatcher`
     *                         instance.
     */
    protected function getEventDispatcher()
    {
        if ($this->dispatcher instanceof EventDispatcher) {
            return $this->dispatcher;
        }

        /** @var \Codeception\Application $app */
        global $app;

        if (! $app instanceof Application) {
            throw new ModuleException(
                $this,
                'Global `app` object is either empty or not an instance of the \Codeception\Application class.'
            );
        }

        /** @var \Codeception\Command\Run $runCommand */
        $runCommand = $app->find('run');

        try {
            /** @var \Codeception\Codecept $codecept */
            $codecept   = ReflectionHelper::readPrivateProperty($runCommand, 'codecept');
            $dispatcher = $codecept->getDispatcher();
        } catch (\ReflectionException $e) {
            throw new ModuleException(
                $this,
                'Could not get the value of the `\Codeception\Command\Run::$codecept` property, message:' .
                $e->getMessage()
            );
        }
        if (! $dispatcher instanceof EventDispatcher) {
            throw new ModuleException($this, sprintf(
                '\\Codeception\\Codecept::$eventDispatcher property is not an instance of %s; value is instead: %s',
                EventDispatcher::class,
                print_r($dispatcher, true)
            ));
        }

        $this->dispatcher = $dispatcher;

        return $this->dispatcher;
    }
}
