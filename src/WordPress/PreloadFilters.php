<?php

namespace lucatume\WPBrowser\WordPress;

use Closure;
use WP_Error;

class PreloadFilters
{
    public static function filterWpDieHandlerToExit(): void
    {
        $throwWPDieException = static function (
            $message,
            $title = '',
            array $args = []
        ): void {
            throw new WPDieException($message, $title, $args);
        };
        self::addFilter('wp_die_handler', static function () use ($throwWPDieException) : Closure {
            return $throwWPDieException;
        }, PHP_INT_MAX);
    }

    /**
     * @param callable|string|array{class-string|object, string} $callback
     */
    public static function addFilter(
        string $hookName,
        $callback,
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

    public static function spoofDnsWildcardCheck(): void
    {
        $callback = static function (bool $preempt, array $parsedArgs, string $url) {
            if (($parsedArgs['method'] ?? 'GET') !== 'GET') {
                return $preempt;
            }

            $siteurl = get_option('siteurl');

            if (!is_string($siteurl)) {
                return $preempt;
            }

            $requestHost = parse_url($url, PHP_URL_HOST);
            $siteHost = parse_url($siteurl, PHP_URL_HOST);

            if (empty($requestHost) || empty($siteHost)) {
                return $preempt;
            }

            if (substr_compare($requestHost, $siteHost, -strlen($siteHost)) !== 0) {
                return $preempt;
            }

            // Return a mock response to avoid the request to the real site.
            return [
                'response' => [
                    'code' => 200,
                    'message' => 'OK',
                ],
                'body' => '',
            ];
        };
        self::addFilter('pre_http_request', $callback, 0, 3);
    }
}
