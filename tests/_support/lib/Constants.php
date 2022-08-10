<?php

namespace tad\Test;

class Constants extends \lucatume\WPBrowser\Environment\Constants
{
    protected array $buffer = [];

    public function __construct(array $buffer = [])
    {
        $this->buffer = $buffer;
    }

    public function defined(string $key): bool
    {
        return isset($this->buffer[$key]);
    }

    public function constant(string $key, mixed $default = null):mixed
    {
        return $this->buffer[$key] ?? $default;
    }
}
