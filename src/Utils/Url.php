<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

use function parse_url;

class Url
{
    /**
     * @var array{
     *     scheme: string,
     *     host: string,
     *     port: int,
     *     user: string,
     *     pass: string,
     *     path: string,
     *     query: string,
     *     fragment: string
     * }
     */
    private static array $parserUrlDefaults = [
        'scheme' => '',
        'host' => '',
        'port' => 0,
        'user' => '',
        'pass' => '',
        'path' => '',
        'query' => '',
        'fragment' => ''
    ];

    /**
     * @return array{
     *     fragment: string,
     *     host: string,
     *     pass: string,
     *     path: string,
     *     port: int,
     *     query: string,
     *     scheme: string,
     *     user: string
     * }
     */
    public static function parseUrl(string $url): array
    {
        $parsed = parse_url($url);

        if (!is_array($parsed)) {
            return self::$parserUrlDefaults;
        }

        $defaults = self::$parserUrlDefaults;
        return [
            'scheme' => (string)($parsed['scheme'] ?? $defaults['scheme']),
            'host' => (string)($parsed['host'] ?? $defaults['host']),
            'port' => (int)($parsed['port'] ?? $defaults['port']),
            'user' => (string)($parsed['user'] ?? $defaults['user']),
            'pass' => (string)($parsed['pass'] ?? $defaults['pass']),
            'path' => (string)($parsed['path'] ?? $defaults['path']),
            'query' => (string)($parsed['query'] ?? $defaults['query']),
            'fragment' => (string)($parsed['fragment'] ?? $defaults['fragment']),
        ];
    }

    public static function getDomain(string $url): string
    {
        $frags = self::parseUrl($url);

        return sprintf(
            '%s%s%s',
            $frags['host'],
            $frags['port'] ? ':' . $frags['port'] : '',
            $frags['path']
        );
    }
}
