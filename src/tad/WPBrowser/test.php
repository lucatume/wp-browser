<?php
/**
 * Test related functions.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

/**
 * Getter and setter to check if debug mode is active, or set the debug mode.
 *
 * @param null|bool $activate Null to read the value, a boolean to activate or deactivate the debug mode.
 *
 * @return bool Whether debug mode is active or not.
 */
function isDebug($activate = null)
{
    static $isDebug;
    if ($activate === null) {
        return (bool)$isDebug;
    }

    $isDebug = (bool)$activate;

    return $isDebug;
}

/**
 * Prints a debug message using Codeception `codecept_debug` function if available.
 *
 * @param string|mixed $message Either a string message or an other value that will be printed JSON encoded.
 *
 * @return void
 */
function debug($message)
{
    if (function_exists('codecept_debug')) {
        codecept_debug($message);
        return;
    }

    if (!isDebug()) {
        return;
    }

    $message = is_string($message) ? $message : json_encode($message);

    echo "\033[34m[Debug] " . $message . "\033[0m\n";
}

/**
 * Ensures a condition else throws an invalid argument exception.
 *
 * @param bool   $condition The condition to assert.
 * @param string $message   The exception message.
 *
 * @return void
 *
 * @throws \InvalidArgumentException If the condition is not met.
 */
function ensure($condition, $message)
{
    if ($condition) {
        return;
    }
    throw new \InvalidArgumentException($message);
}

/**
 * Converts the `preg_last_error` code into human-readable format.
 *
 * @param int $error The `preg_last_error` error code.
 *
 * @return string The `preg_last_error` message, translated in a human-readable form.
 */
function pregErrorMessage($error)
{
    return array_flip(array_filter(get_defined_constants(true)['pcre'], static function ($value) {
        return substr($value, -6) === '_ERROR';
    }, ARRAY_FILTER_USE_KEY))[preg_last_error()];
}
