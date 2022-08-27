<?php

namespace lucatume\WPBrowser\WordPress;

use Closure;
use lucatume\WPBrowser\Exceptions\WPDieException;
use WP_Error;

class Preload
{

    public static function filterWpDieHandlerToExit(): void
    {
        $throwWPDieException = static function (
            string|WP_Error $message,
            string|int $title = '',
            array $args = []
        ) {
            throw new WPDieException($title, $message, $args);
        };
        self::addFilter('wp_die_handler', static fn() => $throwWPDieException, PHP_INT_MAX);
    }

    private static function addFilter(
        string $hookName,
        callable $callback,
        int $priority = 10,
        int $acceptedArgs = 1
    ): void {
        global $wp_filter;
        if (!isset($wp_filter[$hookName])) {
            $wp_filter[$hookName] = [];
        }
        if (!isset($wp_filter['string'][$priority])) {
            $wp_filter[$hookName][$priority] = [];
        }
        $wp_filter[$hookName][$priority][] = [
            'accepted_args' => $acceptedArgs,
            'function' => $callback
        ];
    }
}
