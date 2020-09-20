<?php
/**
 * Models an event fired by the wp-browser package.
 *
 * @package tad\WPBrowser\Events
 */

namespace tad\WPBrowser\Events;

use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * Class WpbrowserEvent
 *
 * @package tad\WPBrowser\Events
 */
class WpbrowserEvent extends SymfonyEvent
{

    /**
     * The event name or handle.
     * @var string
     */
    protected $name;
    /**
     * The event dispatcher.
     *
     * @var object|null
     */
    protected $dispatcher;

    /**
     * Additional context or data for the event.
     *
     * @var array<string,mixed>
     */
    protected $context;

    /**
     * WpbrowserEvent constructor.
     *
     * @param string      $name       The event name or handle.
     * @param object|null         $dispatcher The event dispatcher.
     * @param array<string,mixed> $context    Additional context or data for the event.
     */
    public function __construct($name, $dispatcher = null, array $context = [])
    {
        $this->name       = $name;
        $this->dispatcher = $dispatcher;
        $this->context    = $context;

        /*
         * Assign each context key as property of the event to keep the reference count of object contexts up and avoid
         * garbage collection of objects passed in the context.
         */
        foreach ($context as $key => $value) {
            if (is_object($context[ $key ]) && method_exists($context[ $key ], '__destruct')) {
                codecept_debug(
                    "Object '{$key}' has a __destruct method and will NOT be garbage collected until " .
                    "the event dispatch completed.\n"
                );
            }
            $this->{$key} = $value;
        }
    }

    /**
     * Returns a context value or a default value if teh context does not have an element for that key.
     *
     * @param string $key The context key to return the value for.
     * @param mixed $default The default value to return if the context value for the key is not defined.
     *
     * @return mixed|null The context value for the specified key, or the default value if the context does not have
     *                    a value for the specified key.
     */
    public function get($key, $default = null)
    {
        return isset($this->context[ $key ]) ? $this->context[ $key ] : $default;
    }
}
