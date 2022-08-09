<?php
namespace tad\Test;

class Constants extends \lucatume\WPBrowser\Environment\Constants
{
    protected $buffer = [];

    public function __construct(array $buffer = [])
    {
        $this->buffer = $buffer;
    }

    public function setBuffer(array $buffer)
    {
        $this->buffer = $buffer;
    }

    public function defined($key, $default = null)
    {
        return isset($this->buffer[$key]) ? true : $default;
    }

    public function constant($key, $default = null)
    {
        return isset($this->buffer[$key]) ? $this->buffer[$key] : $default;
    }
}
