<?php
/**
 * An invokable map.
 *
 * Usage:
 * ```php
 * <?php
 * $map = new Map(['foo' => 23, 'bar' => 89]);
 *
 * assert(23 === $map('foo'));
 * assert(89 === $map['bar']);
 * assert(null === $map['baz']);
 * assert(2389 === $map('baz', 2389));
 * ```
 *
 * @package lucatume\WPBrowser\Utils
 */

namespace lucatume\WPBrowser\Utils;

use ArrayAccess;
use ReturnTypeWillChange;

/**
 * Class Map
 *
 * @package lucatume\WPBrowser\Utils
 * @implements ArrayAccess<string,mixed>
 */
class Map implements ArrayAccess
{

    /**
     * Map constructor.
     *
     * @param array<int|string,mixed> $map The map of values underlying this map.
     * @param array<int|string, string> $aliases The map of aliases for the map.
     */
    public function __construct(protected array $map = [], protected array $aliases = [])
    {
    }

    /**
     * Allows invoking the object as if it's a function.
     *
     * @param int|string $key The key to get the value for.
     * @param mixed|null $default The value that will be returned if the key is not set.
     *
     * @return mixed|null The value associated with the key.
     */
    public function __invoke(int|string $key, mixed $default = null): mixed
    {
        $key = $this->redirectAlias($key);
        return $this->map[$key] ?? $default;
    }

    /**
     * Whether a map key exists or not.
     *
     * @param string|int $offset The key of the value to check.
     *
     * @return bool Whether the key is set on the map or not.
     */
    #[ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        $offset = $this->redirectAlias($offset);
        return isset($this->map[$offset]);
    }

    /**
     * Gets a map value.
     *
     * @param string|int $offset The key of the value to get.
     *
     * @return mixed|null The map value of `null` if not found.
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset): mixed
    {
        $offset = $this->redirectAlias($offset);
        return $this->map[$offset] ?? null;
    }

    /**
     * Sets a map value.
     *
     * @param string|int $offset The offset to unset.
     * @param mixed $value The value to set.
     *
     * @return self For chaining.
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, mixed $value): self
    {
        $offset = $this->redirectAlias($offset);
        $this->map[$offset] = $value;

        return $this;
    }

    /**
     * Unsets a  map value.
     *
     * @param string|int $offset The offset to unset.
     *
     * @return self For chaining.
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($offset): self
    {
        $offset = $this->redirectAlias($offset);
        unset($this->map[$offset]);

        return $this;
    }

    /**
     * Returns a value defined in the map, falling back to a default if the value is not defined.
     *
     * @param string|int $offset The name of the value to return from the map.
     * @param mixed|null $default A default value to return if the value associated with the key is not set in the map.
     *
     * @return mixed|null The value associated with the key in the map, or a default value if the key is not set in
     *                    the map.
     */
    public function get(string|int $offset, mixed $default = null): mixed
    {
        $offset = $this->redirectAlias($offset);
        return $this->map[$offset] ?? $default;
    }

    /**
     * Redirects an offset to the real one if the specified offset is an alias.
     *
     * @param string|int $offset The offset to redirect.
     *
     * @return int|string The real offset key.
     */
    protected function redirectAlias(string|int $offset): int|string
    {
        if (array_key_exists($offset, $this->aliases)) {
            $offset = $this->aliases[$offset];
        }

        return $offset;
    }

    /**
     * Sets aliases that will allow calling a value with a different key.
     *
     * @param array<string,string> $aliases A map of each alias and the source key.
     * @param bool $override Whether previous aliases should be overridden or not.
     *
     * @return $this For chaining.
     */
    public function setAliases(array $aliases, bool $override = true): static
    {
        if ($override) {
            $this->aliases = $aliases;
        } else {
            $this->aliases = array_merge($this->aliases, $aliases);
        }

        return $this;
    }

    /**
     * Outputs the map in array format, including aliases.
     *
     * @return array<int|string,mixed> The map in array format, including aliases.
     */
    public function toArray(): array
    {
        $map = $this->map;

        foreach ($this->aliases as $alias => $source) {
            $map[$alias] = $this->map[$source];
        }

        return $map;
    }
}
