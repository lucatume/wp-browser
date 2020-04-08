<?php
/**
 * Support attaching listeners to the Codeception application on Codeception version 4.0+.
 *
 * @package unit\tad\WPBrowser\Extension
 */

namespace tad\WPBrowser\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Extension;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcher;
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
     * Whether the event dispatcher was set on the Event Dispatcher Adapter or not.
     * @var bool
     */
    protected static $dispatcherSet = false;

    /**
     * Whether the event dispatcher should be captured or not for the current suite.
     * @var bool
     */
    protected static $doNotCapture = false;

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
                array_fill(0, count($codeceptionEvents), [ 'onEvent', PHP_INT_MIN ])
            );
        }

        EventDispatcherAdapter::setFallbackAvailable(true);

        return static::$subscribedEvents;
    }

    /**
     * Fires on each events dispatched by Codeception to capture the current Symfony event dispatcher instance.
     */
    public function onEvent()
    {
        if (static::$dispatcherSet || static::$doNotCapture) {
            return;
        }

        $callArgs = func_get_args();

        $event = $callArgs[0];

        $suites = (array) $this->config['suites'];
        if ($event instanceof SuiteEvent && ! empty($suites)) {
            if (! in_array($event->getSuite()->getName(), $suites, true)) {
                static::$doNotCapture = true;
                return;
            }
        }

        foreach ($callArgs as $callArg) {
            if ($callArg instanceof SymfonyEventDispatcher) {
                EventDispatcherAdapter::setWrappedEventDispatcher($callArg);
                static::$dispatcherSet = true;
                return;
            }
        }
    }
}
