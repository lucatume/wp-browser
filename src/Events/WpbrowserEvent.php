<?php
/**
 * Models an event fired by the wp-browser package.
 *
 * @package lucatume\WPBrowser\Events
 */

namespace lucatume\WPBrowser\Events;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class WpbrowserEvent
 *
 * @package lucatume\WPBrowser\Events
 */
class WpbrowserEvent extends Event
{
    public function __construct(
        protected string $name,
        protected ?object $dispatcher = null,
        protected array $context = []
    ) {
        foreach ($context as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Returns a context value by key.
     *
     * @param string $key The context key to return the value for.
     * @param mixed|null $default The default value to return if the key is not found.
     *
     * @return mixed|null The context value for the specified key, or the default value if the context does not have
     *                    a value for the specified key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }
}
