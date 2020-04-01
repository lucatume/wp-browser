<?php
/**
 * Support attaching listeners to the Codeception application on Codeception version 4.0+.
 *
 * @package unit\tad\WPBrowser\Extension
 */

namespace tad\WPBrowser\Extension;

use Codeception\Extension;
use tad\WPBrowser\Events\EventDispatcherAdapter;

/**
 * Class Events
 *
 * @package unit\tad\WPBrowser\Extension
 */
class Events extends Extension
{

    /**
     * A compiled list of all the events this extension subscribes to.
     * @var array|false
     */
    protected static $subscribedEvents;

    /**
     * Returns a map of all Codeception events, each calling the `__call` magic method.
     *
     * @return array<string,string> A map of all Codeception events to the magic `__call` proxy.
     */
    public static function getSubscribedEvents()
    {
        if (static::$subscribedEvents === null) {
            $codeceptionEvents = EventDispatcherAdapter::codeceptionEvents();

            static::$subscribedEvents = array_combine(
                $codeceptionEvents,
                array_map(static function ($eventName) {
                    // 'suite.before' to 'suite_before'.
                    return str_replace('.', '_', $eventName);
                }, $codeceptionEvents)
            );
        }

        EventDispatcherAdapter::setFallbackAvailable(true);

        return static::$subscribedEvents;
    }

    /**
     * Implementation of the magic method to proxy calls ot methods like `suite_before` to the `dispatchEvent` method.
     *
     * @param string $name The name of the called method.
     * @param array  $args The method call arguments.
     */
    public function __call($name, $args)
    {
        // 'suite_before' to 'suite.before'.
        $eventName = str_replace('_', '.', $name);
        call_user_func_array([ $this, 'dispatchEvent' ], array_merge($eventName, $args));
    }

    /**
     * Dispatches the event using the shared event dispatcher instance.
     *
     * @since TBD
     *
     * @param string      $eventName The name of the event to dispatch.
     * @param object|null $event     The event object being dispatched.
     * @param mixed|null Additional context for the dispatch.
     */
    public function dispatchEvent($eventName, $event = null, $context = null)
    {
        EventDispatcherAdapter::getEventDispatcher()->dispatch($eventName, $event, $context);
    }
}
