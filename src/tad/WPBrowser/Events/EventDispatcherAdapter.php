<?php
/**
 * A wrapper of Symfony Event Dispatcher to deal with different versions of the framework.
 *
 * @package tad\WPBrowser\Events
 */

namespace tad\WPBrowser\Events;

use Codeception\Application;
use Codeception\Codecept;
use Codeception\Exception\TestRuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use function tad\WPBrowser\readPrivateProperty;

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
     * The shared instance of the adapter.
     *
     * @var static|null
     */
    protected static $sharedInstance;

    /**
     * Whether using this API to attach listeners to Codeception default events is allowed or not.
     *
     * @var bool
     */
    protected static $allowCodeceptionHooks;
    /**
     * A list of Codeception events, compiled at run-time.
     *
     * @var array<string>
     */
    protected static $compiledCodeceptionEvents;

    /**
     * A list of all the possible Codeception events.
     *
     * @var array<string>
     */
    protected static $allCodeceptionEvents = [
        '\Codeception\Events::MODULE_INIT',
        '\Codeception\Events::TEST_BEFORE',
        '\Codeception\Events::TEST_START',
        '\Codeception\Events::SUITE_BEFORE',
        '\Codeception\Events::STEP_BEFORE',
        '\Codeception\Events::SUITE_INIT',
        '\Codeception\Events::RESULT_PRINT_AFTER',
        '\Codeception\Events::STEP_AFTER',
        '\Codeception\Events::SUITE_AFTER',
        '\Codeception\Events::TEST_AFTER',
        '\Codeception\Events::TEST_END',
        '\Codeception\Events::TEST_ERROR',
        '\Codeception\Events::TEST_FAIL',
        '\Codeception\Events::TEST_FAIL_PRINT',
        '\Codeception\Events::TEST_INCOMPLETE',
        '\Codeception\Events::TEST_PARSED',
        '\Codeception\Events::TEST_SKIPPED',
        '\Codeception\Events::TEST_SUCCESS',
        '\Codeception\Events::TEST_WARNING'
    ];

    /**
     * Whether the fallback provided by the Events extension to attach listeners to Codeception 4.0+ events is available
     * or not.
     * @var bool
     */
    protected static $fallbackAvailable = false;

    /**
     * The wrapped Symfony Event Dispatcher instance.
     *
     * @var SymfonyEventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * EventDispatcherAdapter constructor.
     *
     * @param SymfonyEventDispatcherInterface $eventDispatcher The Symfony Event Dispatcher instance to wrap.
     */
    public function __construct(SymfonyEventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Returns the shared instance of the event dispatcher that will be shared by all classes dispatching and listening
     * for events.
     *
     * @return EventDispatcherAdapter The shared instance of this class.
     */
    public static function getEventDispatcher()
    {
        if (static::$sharedInstance !== null) {
            return static::$sharedInstance;
        }

        global $app;

        $appEventDispatcher = null;
        if ($app instanceof Application) {
            static::$allowCodeceptionHooks = true;
            $appEventDispatcher            = static::getAppEventDispatcher($app);
        } else {
            static::$allowCodeceptionHooks = false;
            $appEventDispatcher            = new SymfonyEventDispatcher();
        }

        if (! $appEventDispatcher instanceof SymfonyEventDispatcher) {
            throw new TestRuntimeException(sprintf(
                '\\Codeception\\Codecept::$eventDispatcher property is not an instance of %s; value is instead: %s',
                SymfonyEventDispatcher::class,
                print_r($appEventDispatcher, true)
            ));
        }

        static::$sharedInstance = new self($appEventDispatcher);

        return static::$sharedInstance;
    }

    /**
     * Returns the global Codeception application event dispatcher.
     *
     * @param Application|null $app Either the specific application, or `null` to default to the global one.
     *
     * @return SymfonyEventDispatcher|null Either the event dispatcher used by the global application, or `null`
     *                                     if the global application is not defined.
     *
     * @throws TestRuntimeException If the global application, or one of its expected properties, are not the expected
     *                              type.
     */
    protected static function getAppEventDispatcher(Application $app = null)
    {
        if ($app === null) {
            global $app;
        }

        if (! $app instanceof Application) {
            return null;
        }

        try {
            $runningCommand = readPrivateProperty($app, 'runningCommand');

            if (! $runningCommand instanceof Command) {
                throw new TestRuntimeException(
                    'Running command is empty or not an instance of the ' .
                    'Symfony\Component\Console\Command\Command class.'
                );
            }

            $codecept = readPrivateProperty($runningCommand, 'codecept');

            if (! $codecept instanceof Codecept) {
                throw new TestRuntimeException(
                    'Running command $codecept property is not set or not the correct type.'
                );
            }

            $appDispatcher = $codecept->getDispatcher();
        } catch (\ReflectionException $e) {
            throw new TestRuntimeException(
                'Could not get the value of the command $codecept property, message:' .
                $e->getMessage()
            );
        }

        return $appDispatcher;
    }

    /**
     * Resets the shared instance.
     *
     * @return void
     */
    public static function resetSharedInstance()
    {
        static::$sharedInstance = null;
    }

    /**
     * Sets the event dispatcher the shared instance should use.
     *
     * @param SymfonyEventDispatcherInterface $eventDispatcher The event dispatcher to set on the shared instance.
     *
     * @return void
     */
    public static function setWrappedEventDispatcher(SymfonyEventDispatcherInterface $eventDispatcher)
    {
        if (static::$sharedInstance === null) {
            static::$sharedInstance = new self($eventDispatcher);

            return;
        }

        static::$sharedInstance->eventDispatcher = $eventDispatcher;
    }

    /**
     * Returns a list of all the available Codeception events, compiled at run-time and cached.
     *
     * @return array<string> A list of all the available Codeception events.
     */
    public static function codeceptionEvents()
    {
        if (null === static::$compiledCodeceptionEvents) {
            static::$compiledCodeceptionEvents = array_filter(array_map(static function ($const) {
                return defined($const) ? constant($const) : null;
            }, static::$allCodeceptionEvents));
        }

        return static::$compiledCodeceptionEvents;
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
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the event is a Codeception one and listeners cannot be attached to
     *                                   Codeception default events due to the Codeception version.
     */
    public function addListener($eventName, callable $listener, $priority = 0)
    {
        static::checkEventName($eventName);
        $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * Checks an event name to make sure the current version of Codeception does support it.
     *
     * @param string $eventName The name of the event to check.
     *
     * @throws \InvalidArgumentException If the event is a Codeception one and listeners cannot be attached to
     *                                   Codeception default events due to the Codeception version.
     *
     * @return void
     */
    protected static function checkEventName($eventName)
    {
        if (static::isCodeceptionEvent($eventName)
            && !( static::$allowCodeceptionHooks || static::$fallbackAvailable )
        ) {
            $message = <<< OUT
Cannot attach listeners to '{$eventName}'; this version of Codeception does not allow it.

If you need to attach listeners to Codeception events you can:

  1. Create a custom module: https://codeception.com/docs/06-ModulesAndHelpers#Hooks
  2. Create a custom extension: https://codeception.com/extensions
  3. Add the '\tad\WPBrowser\Extension\Events' extension to Codeception configuration file:
    ```
    extensions:
      enabled:
        - tad\WPBrowser\Extension\Events
    ```
OUT;

            throw new \InvalidArgumentException($message);
        }
    }

    /**
     * Checks whether an event is a Codeception event or not.
     *
     * @param string $eventName The name of the event to check.
     *
     * @return bool Whether an event is a Codeception event or not.
     */
    public static function isCodeceptionEvent($eventName)
    {
        return in_array($eventName, static::codeceptionEvents(), true);
    }

    /**
     * Returns whether an alternative way to attach listeners to the Codeception application events is available or
     * not.
     *
     * @return bool Whether an alternative way to attach listeners to the Codeception application events is available or
     *              not.
     */
    protected static function fallbackAvailable()
    {
        return static::$fallbackAvailable;
    }

    /**
     * Sets whether the fallback to attache listeners to Codeception 4.0+ events is available or not.
     *
     * @param bool $fallbackAvailable Whether the fallback to attache listeners to Codeception 4.0+ events is available
     *                                or not.
     *
     * @return void
     */
    public static function setFallbackAvailable($fallbackAvailable)
    {
        static::$fallbackAvailable = (bool) $fallbackAvailable;
    }

    /**
     * Dispatches an event using the correct parameter order depending on the current Symfony component version.
     *
     * @param string                   $eventName     The event name or handle.
     * @param object|SymfonyEvent|null $originOrEvent The event origin or, in the case of events dispatched by
     *                                                Codeception, the original dispatched event.
     * @param array<string,mixed>      $context       Additional context or data for the event.
     *
     * @return object The passed `$event` MUST be returned
     */
    public function dispatch($eventName, $originOrEvent = null, array $context = [])
    {
        // Only create a new event if the origin is not already itself an event.
        $eventObject = $originOrEvent instanceof SymfonyEvent ?
            $originOrEvent
            : new WpbrowserEvent($eventName, $originOrEvent, $context);

        // Depending on the Symfony Event Dispatcher version, change the order of the dispatch arguments.
        $dispatchArgs = static::dispatchWithObject() ?
            [ $eventObject, $eventName ]
            : [ $eventName, $eventObject ];

        // @phpstan-ignore-next-line
        return $this->eventDispatcher->dispatch(...$dispatchArgs);
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
        if (self::$dispatchWithObject !== null) {
            return self::$dispatchWithObject;
        }

        try {
            $methodReflection = new \ReflectionMethod(SymfonyEventDispatcher::class, 'dispatch');
        } catch (\ReflectionException $e) {
            self::$dispatchWithObject = false;

            return self::$dispatchWithObject;
        }

        $methodArguments          = $methodReflection->getParameters();
        $firstArgument            = count($methodArguments) ? reset($methodArguments) : false;
        self::$dispatchWithObject = false;

        if ($firstArgument instanceof \ReflectionParameter) {
            self::$dispatchWithObject = $firstArgument->getName() !== 'eventName';
        }

        return self::$dispatchWithObject;
    }

    /**
     * Returns the original Symfony Event Dispatcher wrapped by the adapter.
     *
     * @return SymfonyEventDispatcherInterface The original Symfony Event Dispatcher wrapped by the adapter.
     */
    public function getOriginalEventDispatcher()
    {
        return $this->eventDispatcher;
    }
}
