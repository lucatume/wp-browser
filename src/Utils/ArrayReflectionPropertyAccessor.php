<?php

namespace lucatume\WPBrowser\Utils;

use ArrayAccess;
use ReflectionProperty;

// @phpstan-ignore-next-line
class ArrayReflectionPropertyAccessor implements ArrayAccess
{
    public function __construct(private ReflectionProperty $arrayProp, private object $object)
    {
    }

    public function offsetExists($offset): bool
    {
        /** @var array<string|int,mixed> $current */
        $current = $this->arrayProp->getValue($this->object);

        return isset($current, $offset);
    }

    public function offsetGet($offset): mixed
    {
        /** @var array<string|int,mixed> $current */
        $current = $this->arrayProp->getValue($this->object);

        return $current[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        /** @var array<string|int,mixed> $current */
        $current = $this->arrayProp->getValue($this->object);
        $current[$offset] = $value;
        $this->arrayProp->setValue($this->object, $current);
    }

    public function offsetUnset($offset): void
    {
        /** @var array<string|int,mixed> $current */
        $current = $this->arrayProp->getValue($this->object);
        unset($current[$offset]);
        $this->arrayProp->setValue($this->object, $current);
    }
}
