<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

class Url
{
    /**
     * @var array{scheme: string, host: string, port: int, user: string, pass: string, path: string, query: string, fragment: string}
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
     * @return array{scheme: string, host: string, port: int, user: string, pass: string, path: string, query: string, fragment: string}
     */
    public static function parseUrl(string $url): array
    {
        $parsed = \parse_url($url);

        if (!is_array($parsed)) {
            return self::$parserUrlDefaults;
        }

        return array_replace(self::$parserUrlDefaults, $parsed);
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
