<?php
/**
 * Provides methods to interact with Codeception `run` command event dispatch stack.
 *
 * @package tad\WPBrowser\Traits
 */

namespace tad\WPBrowser\Traits;

use Codeception\Exception\TestRuntimeException;
use tad\WPBrowser\Events\EventDispatcherAdapter;

/**
 * Trait EventListener
 *
 * @package tad\WPBrowser\Module\Traits
 */
trait WithEvents
{
    /**
     * Adds a callback to be performed on a global runner event..
     *
     * @param string   $eventName The event to run the callback on.
     * @param callable $listener  The callback to run on the event.
     * @param int      $priority  The priority that will be assigned to the callback in the context of the event.
     *
     * @throws \Codeception\Exception\TestRuntimeException If the event dispatcher cannot be found or built.
     */
    protected function addAction($eventName, $listener, $priority = 0)
    {
        $this->getEventDispatcher()->addListener($eventName, $listener, $priority);
    }

    /**
     * Returns the instance of the event dispatcher used by the currently running command instance.
     *
     * If no command is currently running, then a shared event dispatcher is built and will be returned to all
     * the classes using the trait.
     *
     * @return EventDispatcherAdapter The event dispatcher instance used by the running command or one created ad-hoc.
     *                         The event dispatcher instance is shared by all instances implementing the trait.
     *
     * @throws TestRuntimeException If the global application instance is not a Codeception\Application instance; if the
     *                         `run` command dispatcher property cannot be accessed or is not an `EventDispatcher`
     *                         instance.
     */
    protected function getEventDispatcher()
    {
        return EventDispatcherAdapter::getEventDispatcher();
    }
}
