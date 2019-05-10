<?php

namespace tad\WPBrowser\Environment;

/**
 * Class Constants
 *
 * Wrapper around constants operations.
 *
 * @package tad\WPBrowser\Environment
 */
class Constants
{

    public function defined($key)
    {
        return defined($key);
    }

    public function define($key, $value)
    {
        return define($key, $value);
    }

    public function constant($key)
    {
        return constant($key);
    }

    public function defineIfUndefined($key, $value)
    {
        if (defined($key)) {
            return;
        }
        define($key, $value);
    }
}
