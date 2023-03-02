<?php
declare(strict_types=1);

namespace lucatume\WPBrowser\Events;

use Symfony\Component\Console\Command\Command;
use Codeception\Codecept;
use lucatume\WPBrowser\Utils\Property;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

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
            } catch (\ReflectionException $e) {
            }
        }

        return self::$codeceptInstance;
    }

    private static function getCodeceptionEventDispatcher(): ?EventDispatcherInterface
    {
        if (self::$codeceptionEventDispatcherInstance !== null) {
            return self::$codeceptionEventDispatcherInstance;
        }

        $codeceptInstance = self::getCodecept();

        if ($codeceptInstance === null) {
            return null;
        }

        try {
            self::$codeceptionEventDispatcherInstance = Property::readPrivate($codeceptInstance, 'dispatcher');
        } catch (\ReflectionException $e) {
            return null;
        }

        return self::$codeceptionEventDispatcherInstance;
    }

    /**
     * Adds a callback to be performed on a global runner event.
     *
     * The method name recalls the WordPress framework `add_action` function as it works pretty muche the same.
     *
     * @param string   $eventName The event to run the callback on.
     * @param callable $listener  The callback to run on the event.
     * @param int      $priority  The priority that will be assigned to the callback in the context of the event.
     *
     * @return void
     * @throws RuntimeException If the event dispatcher cannot be found or built.
     *
     */
    public static function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        self::getCodeceptionEventDispatcher()?->addListener($eventName, $listener, $priority);
    }

    /**
     * Dispatches an action using the global event dispatcher.
     *
     * The method name recalls the WordPress framework `do_action` function as it works pretty muche the same.
     *
     * @param string              $name      The name of the event to dispatch.
     * @param mixed|null          $origin    The event origin: an object, a string or null.
     * @param array<string,mixed> $context   A map of the event context that will set as context of the dispatched
     *                                       event.
     *
     * @return object The dispatched event.
     *
     * @throws EventDispatcherException If the Codeception application event dispatcher cannot be found or built.
     */
    public static function dispatch(string $name, mixed $origin = null, array $context = []): object
    {
        $event = new Event($name, $context, $origin);

        $eventDispatcher = self::getCodeceptionEventDispatcher();

        if (!$eventDispatcher instanceof EventDispatcherInterface) {
            throw new EventDispatcherException('Could not find the Codeception event dispatcher.');
        }

        return $eventDispatcher?->dispatch($event, $name);
    }
}
