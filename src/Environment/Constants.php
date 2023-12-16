<?php
/**
 * A wrapper around constants to allow injection and testing.
 *
 * @package lucatume\WPBrowser\Environment
 */

namespace lucatume\WPBrowser\Environment;

/**
 * Class Constants
 *
 * Wrapper around constants operations.
 *
 * @package lucatume\WPBrowser\Environment
 */
class Constants
{

    /**
     * Returns whether a constant is defined or not.
     *
     * @param string $key The name of the constant to check on.
     *
     * @return bool Whether the constant is defined or not.
     */
    public function defined(string $key): bool
    {
        return defined($key);
    }

    /**
     * Defines a constant.
     *
     * @param string $key The constant to define.
     * @param mixed $value The constant value.
     *
     * @return bool Whether the constant was defined or not.
     */
    public function define(string $key, mixed $value): bool
    {
        return define($key, $value);
    }

    /**
     * Returns the value of a constant.
     *
     * @param string $key The value of a constant
     * @param mixed|null $default The default value to return if the constant is not defined.
     *
     * @return mixed The value of the constant, if defined, or the default value.
     */
    public function constant(string $key, mixed $default = null): mixed
    {
        return defined($key) ? constant($key) : $default;
    }

    /**
     * Defines a constant, if undefined.
     *
     * @param string $key The name of the constant to define.
     * @param mixed $value The value to set for the constant.
     */
    public function defineIfUndefined(string $key, mixed $value): void
    {
        if (defined($key)) {
            return;
        }
        define($key, $value);
    }
}
