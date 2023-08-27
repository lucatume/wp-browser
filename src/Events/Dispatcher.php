<?php
declare(strict_types=1);

namespace lucatume\WPBrowser\Events;

use Closure;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Utils\Property;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Dispatcher
{
    private static ?EventDispatcherInterface $eventDispatcher = null;

    public static function setEventDispatcher(EventDispatcherInterface $eventDispatcher = null): void
    {
        $previousEventDispatcher = self::$eventDispatcher;

        if ($previousEventDispatcher === $eventDispatcher) {
            return;
        }

        if ($eventDispatcher === null) {
            self::$eventDispatcher = null;
            return;
        }

        if ($previousEventDispatcher !== null) {
            /** @var array<callable[]> $previousListeners */
            $previousListeners = $previousEventDispatcher->getListeners();
            foreach ($previousListeners as $eventName => $listeners) {
                foreach ($listeners as $listener) {
                    $priority = $previousEventDispatcher->getListenerPriority($eventName, $listener);
                    $eventDispatcher->addListener($eventName, $listener, $priority ?? 0);
                }
            }
        }

        self::$eventDispatcher = $eventDispatcher;
    }

    public static function getEventDispatcher(): ?EventDispatcherInterface
    {
        if (self::$eventDispatcher === null) {
            // Create a one local to the Dispatcher.
            self::$eventDispatcher = new EventDispatcher();
        }

        return self::$eventDispatcher;
    }

    /**
     * Adds a callback to be performed on a global runner event, or on a local one if the global one cannot be found.
     *
     * The method name recalls the WordPress framework `add_action` function as it works pretty much the same.
     *
     * @param string $eventName  The event to run the callback on.
     * @param callable $listener The callback to run on the event.
     * @param int $priority      The priority that will be assigned to the callback in the context of the event.
     *
     * @return Closure The callback to remove the listener from the event.
     *
     * @throws RuntimeException If the event dispatcher cannot be found or built.
     */
    public static function addListener(string $eventName, callable $listener, int $priority = 0): Closure
    {
        self::getEventDispatcher()?->addListener($eventName, $listener, $priority);

        return static function () use ($eventName, $listener): void {
            self::getEventDispatcher()?->removeListener($eventName, $listener);
        };
    }

    /**
     * Dispatches an action using the global event dispatcher; or a local one if the global one cannot be found.
     *
     * The method name recalls the WordPress framework `do_action` function as it works pretty much the same.
     *
     * @param string $name                   The name of the event to dispatch.
     * @param mixed|null $origin             The event origin: an object, a string or null.
     * @param array<string,mixed> $context   A map of the event context that will set as context of the dispatched
     *                                       event.
     *
     * @return object|null The dispatched event, or `null` if no event was dispatched.
     */
    public static function dispatch(string $name, mixed $origin = null, array $context = []): ?object
    {
        $event = new Event($name, $context, $origin);

        return self::getEventDispatcher()?->dispatch($event, $name);
    }
}
