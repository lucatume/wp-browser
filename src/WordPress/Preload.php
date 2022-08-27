<?php

namespace lucatume\WPBrowser\WordPress;

use Closure;
use WP_Error;

class WordPressPreLoad
{

    public static function filterWpDieHandlerToExit(): Closure
    {
        $closure = static function (
            string|WP_Error $message,
            string|int $title = '',
            array $args = []
        ) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $message = $message instanceof WP_Error ? $message->get_error_message() : $message;
            $title = is_numeric($title) ? '' : $title;
            if ($title) {
                echo '[wp_die] ', $title, ': ', $message;
            } else {
                echo '[wp_die] ', $message;
            }
            exit((is_numeric($title)) ? $title : 1);
        };

        global $wp_filter;
        $wp_filter = [
            'wp_die_handler' => [
                PHP_INT_MAX => [
                    [
                        'accepted_args' => 1,
                        'function' => static function () use ($closure) {
                            return
                                $closure;
                        }
                    ]
                ]
            ],
        ];

        return $closure;
    }
}
