<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

class Url
{
    public static function parseUrl(string $url): array
    {
        $parsed = \parse_url($url);

        if (!is_array($parsed)) {
            throw new \InvalidArgumentException("Failed to parse URL {$url}");
        }

        return array_replace([
            'scheme' => '',
            'host' => '',
            'port' => 0,
            'user' => '',
            'pass' => '',
            'path' => '',
            'query' => '',
            'fragment' => ''
        ], $parsed);
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
