<?php
/**
 * A wrapper of Symfony Event Dispatcher to deal with different versions of the framework.
 *
 * @package tad\WPBrowser\Events
 */

namespace tad\WPBrowser\Events;

use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

/**
 * Class EventDispatcherAdapter
 *
 * @package tad\WPBrowser\Events
 */
class EventDispatcherAdapter
{
    /**
     * Whether events should be dispatched using an event object as first argument (newer Symfony versions) or not.
     * @var bool
     */
    protected static $dispatchWithObject;
    /**
     * The wrapped Symfony Event Dispatcher instance.
     * @var SymfonyEventDispatcher
     */
    protected $eventDispatcher;

    /**
     * EventDispatcherAdapter constructor.
     *
     * @param SymfonyEventDispatcher $eventDispatcher The Symfony Event Dispatcher instance to wrap.
     */
    public function __construct(SymfonyEventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Adds a listener to the current event dispatcher for a specific event.
     *
     * @param string   $eventName   The name of the event to add a listener for.
     * @param callable $listener    A callable closure or method that will be called when the event is dispatched. The
     *                              listener will receive the following arguments: object `$event`, string `$eventName`
     *                              and `$eventDispatcher` as last argument.
     * @param int      $priority    A priority to attach the listener at. Differently from WordPress listeners added at
     *                              higher priorities are called first.
     */
    public function addListener($eventName, callable $listener, $priority)
    {
        $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * Dispatches an event using the correct parameter order depending on the current Symfony component version.
     *
     * @param string      $name       The event name or handle.
     * @param object|null $dispatcher The event dispatcher.
     * @param array       $context    Additional context or data for the event.
     */
    public function dispatch($eventName, $origin = null, array $context = [])
    {
        $eventObject = new WpbrowserEvent($eventName, $origin, $context);

        if ($this->dispatchWithObject()) {
            $this->eventDispatcher->dispatch($eventObject, $eventName);

            return;
        }

        $this->eventDispatcher->dispatch($eventName, $eventObject);
    }

    /**
     * Returns whether the adapter will dispatch events with an object first argument or not.
     *
     * The choice is done at run-time depending on the signature of the
     * `Symfony\Component\EventDispatcher\EventDispatcher::dispatch` method.
     * The results is then statically cached.
     *
     * @return bool Whether events will be dispatched with an object first arguments (newer Symfony versions) or not.
     */
    public static function dispatchWithObject()
    {
        if (static::$dispatchWithObject !== null) {
            return static::$dispatchWithObject;
        }

        try {
            $methodReflection = new \ReflectionMethod(SymfonyEventDispatcher::class, 'dispatch');
        } catch (\ReflectionException $e) {
            static::$dispatchWithObject = false;

            return static::$dispatchWithObject;
        }

        $methodArguments            = $methodReflection->getParameters();
        $firstArgument              = count($methodArguments) ? reset($methodArguments) : false;
        static::$dispatchWithObject =  false;

        if ($firstArgument instanceof \ReflectionParameter) {
            $type                       = $firstArgument->getType();
            static::$dispatchWithObject = $type !== null && $type->__toString() === 'object';
        }

        return static::$dispatchWithObject;
    }

    /**
     * Returns the original Symfony Event Dispatcher wrapped by the adapter.
     *
     * @return SymfonyEventDispatcher The original Symfony Event Dispatcher wrapped by the adapter.
     */
    public function getOriginalEventDispatcher()
    {
        return $this->eventDispatcher;
    }
}
