<?php
/**
 * Functions related to listening and dispatching events using wp-browser events API.
 *
 * @package lucatume\WPBrowser
 */

namespace lucatume\WPBrowser;

use lucatume\WPBrowser\Events\EventDispatcherAdapter;

/**
 * Adds a listener for a specific event.
 *
 * @param string   $eventName   The name of the event to add a listener for.
 * @param callable $listener    A callable closure or method that will be called when the event is dispatched. The
 *                              listener will receive the following arguments: object `$event`, string `$eventName`
 *                              and `$eventDispatcher` as last argument.
 * @param int      $priority    A priority to attach the listener at. Differently from WordPress listeners added at
 *                              higher priorities are called first.
 *
 * @throws \InvalidArgumentException If the event is a Codeception one and listeners cannot be attached to
 *                                   Codeception default events due to the Codeception version.
 */
function addListener($eventName, callable $listener, $priority = 0): void
{
    EventDispatcherAdapter::getEventDispatcher()->addListener($eventName, $listener, $priority);
}

/**
 * Dispatches an event using wp-browser Events API.
 *
 * @param string       $eventName The event name or handle.
 * @param object|null  $origin    The dispatched event origin.
 * @param array<mixed> $context   Additional context or data for the event.
 */
function dispatch($eventName, $origin = null, array $context = []): void
{
    EventDispatcherAdapter::getEventDispatcher()->dispatch($eventName, $origin, $context);
}
