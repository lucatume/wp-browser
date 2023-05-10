<?php
declare(strict_types=1);

namespace lucatume\WPBrowser\Events;

use Closure;
use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Codeception\Codecept;
use lucatume\WPBrowser\Utils\Property;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Dispatcher
{
    private static ?Codecept $codeceptInstance = null;
    private static ?EventDispatcherInterface $codeceptionEventDispatcherInstance = null;

    private static function getCodecept(): ?Codecept
    {
        if (self::$codeceptInstance !== null) {
            return self::$codeceptInstance;
        }

        $reverseBacktraceHead = array_reverse(
            array_slice(
                array_reverse(
                    debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT)
                ),
                0,
                20)
        );

        foreach ($reverseBacktraceHead as $backtraceEntry) {
            $object = $backtraceEntry['object'] ?? null;

            if (!$object instanceof Command) {
                continue;
            }

            try {
                $codeceptInstance = Property::readPrivate($object, 'codecept');

                if (!$codeceptInstance instanceof Codecept) {
                    continue;
                }

                self::$codeceptInstance = $codeceptInstance;
                return self::$codeceptInstance;
            } catch (ReflectionException) {
            }
        }

        return self::$codeceptInstance;
    }

    private static function getCodeceptionEventDispatcher(): EventDispatcherInterface
    {
        if (self::$codeceptionEventDispatcherInstance !== null) {
            return self::$codeceptionEventDispatcherInstance;
        }

        $codeceptInstance = self::getCodecept();

        if ($codeceptInstance === null) {
            // Create a one local to the Dispatcher.
            self::$codeceptionEventDispatcherInstance = new EventDispatcher();
            return self::$codeceptionEventDispatcherInstance;
        }

        try {
            self::$codeceptionEventDispatcherInstance = Property::readPrivate($codeceptInstance, 'dispatcher');
        } catch (ReflectionException) {
            // Create a one local to the Dispatcher.
            self::$codeceptionEventDispatcherInstance = new EventDispatcher();
        }

        return self::$codeceptionEventDispatcherInstance;
    }

    /**
     * Adds a callback to be performed on a global runner event, or on a local one if the global one cannot be found.
     *
     * The method name recalls the WordPress framework `add_action` function as it works pretty much the same.
     *
     * @param string   $eventName The event to run the callback on.
     * @param callable $listener  The callback to run on the event.
     * @param int      $priority  The priority that will be assigned to the callback in the context of the event.
     *
     * @return Closure The callback to remove the listener from the event.
     *
     * @throws RuntimeException If the event dispatcher cannot be found or built.
     */
    public static function addListener(string $eventName, callable $listener, int $priority = 0): Closure
    {
        self::getCodeceptionEventDispatcher()->addListener($eventName, $listener, $priority);

        return static function () use ($eventName, $listener): void {
            self::getCodeceptionEventDispatcher()->removeListener($eventName, $listener);
        };
    }

    /**
     * Dispatches an action using the global event dispatcher; or a local one if the global one cannot be found.
     *
     * The method name recalls the WordPress framework `do_action` function as it works pretty much the same.
     *
     * @param string              $name      The name of the event to dispatch.
     * @param mixed|null          $origin    The event origin: an object, a string or null.
     * @param array<string,mixed> $context   A map of the event context that will set as context of the dispatched
     *                                       event.
     *
     * @return object The dispatched event.
     */
    public static function dispatch(string $name, mixed $origin = null, array $context = []): object
    {
        $event = new Event($name, $context, $origin);

        return self::getCodeceptionEventDispatcher()->dispatch($event, $name);
    }
}
