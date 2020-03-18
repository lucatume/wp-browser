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
 * @package tad\WPBrowser\Utils
 */

namespace tad\WPBrowser\Utils;

/**
 * Class Map
 *
 * @package tad\WPBrowser\Utils
 */
class Map implements \ArrayAccess
{

    /**
     * The map of value underlying the map.
     *
     * @var array<string,mixed>
     */
    protected $map = [];

    /**
     * Map constructor.
     *
     * @param array<string,mixed> $map The map of values underlying this map.
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * Allows invoking the object as if it's a function.
     *
     * @param string     $key     The key to get the value for.
     * @param null|mixed $default The value that will be returned if the key is not set.
     * @return mixed|null The value associated with the key.
     */
    public function __invoke($key, $default = null)
    {
        return isset($this->map[$key]) ? $this->map[$key] : $default;
    }


    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return isset($this->map[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return isset($this->map[$offset]) ? $this->map[$offset] : null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $this->map[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->map[$offset]);
    }

    /**
     * Sets the underlying map this instance should use.
     *
     * @param array<string,mixed> $map The map this object should use.
     */
    public function setMap(array $map)
    {
        $this->map = $map;
    }
}
