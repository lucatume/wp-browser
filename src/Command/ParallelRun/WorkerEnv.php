<?php

namespace lucatume\WPBrowser\Command\ParallelRun;

use lucatume\WPBrowser\Utils\Env;

/**
 * @internal
 */
final class WorkerEnv
{
    public const PORT_STRIDE = 10;

    private const BASE_PORTS = [
        'WORDPRESS_LOCALHOST_PORT' => 2389,
        'CHROMEDRIVER_PORT'        => 2390,
    ];

    private const EXTENSION_PORTS = [
        'lucatume\\WPBrowser\\Extension\\ChromeDriverController'  => 'CHROMEDRIVER_PORT',
        'lucatume\\WPBrowser\\Extension\\BuiltInServerController' => 'WORDPRESS_LOCALHOST_PORT',
    ];

    private const REWRITE_NEEDLES = [
        'WORDPRESS_URL'           => ':2389',
        'WORDPRESS_DOMAIN'        => ':2389',
        'WORDPRESS_SUBDIR_URL'    => ':2389',
        'WORDPRESS_SUBDOMAIN_URL' => ':2389',
    ];

    private const DB_NAME_KEYS = [
        'WORDPRESS_DB_NAME',
        'WORDPRESS_SUBDIR_DB_NAME',
        'WORDPRESS_SUBDOMAIN_DB_NAME',
        'WORDPRESS_EMPTY_DB_NAME',
    ];

    private const DB_DSN_KEYS = [
        'WORDPRESS_DB_DSN',
        'WORDPRESS_SUBDIR_DB_DSN',
        'WORDPRESS_SUBDOMAIN_DB_DSN',
        'WORDPRESS_EMPTY_DB_DSN',
    ];

    /**
     * @param array<string,mixed>   $baseEnv
     * @param array<string,int>|null $portOverrides Map of BASE_PORTS keys to chosen ports; falls back to stride math.
     * @return array<string,string>
     */
    public static function build(
        int $workerIndex,
        array $baseEnv,
        ?string $envFilePath = null,
        ?array $portOverrides = null
    ): array {
        $env = self::stringifyEnv($baseEnv);

        if ($envFilePath !== null && is_file($envFilePath)) {
            foreach (Env::envFile($envFilePath) as $k => $v) {
                $env[(string)$k] = (string)$v;
            }
        }

        $ports = self::resolvePorts($workerIndex, $portOverrides);
        foreach ($ports as $name => $port) {
            $env[$name] = (string)$port;
        }

        foreach (self::REWRITE_NEEDLES as $key => $needle) {
            if (!isset($env[$key])) {
                continue;
            }
            $newPort = $ports['WORDPRESS_LOCALHOST_PORT'];
            $replace = str_replace('2389', (string)$newPort, $needle);
            $env[$key] = str_replace($needle, $replace, $env[$key]);
        }

        // Suffix all known DB name vars with _w{workerIndex}
        foreach (self::DB_NAME_KEYS as $nameKey) {
            if (isset($env[$nameKey])) {
                $env[$nameKey] .= '_w' . $workerIndex;
            }
        }

        // Rewrite dbname= in DSN strings
        foreach (self::DB_DSN_KEYS as $dsnKey) {
            if (isset($env[$dsnKey])) {
                $env[$dsnKey] = preg_replace(
                    '/dbname=([^;]+)/',
                    'dbname=$1_w' . $workerIndex,
                    $env[$dsnKey]
                ) ?? $env[$dsnKey];
            }
        }

        // Rewrite database name in URL path
        if (isset($env['WORDPRESS_DB_URL'])) {
            $env['WORDPRESS_DB_URL'] = preg_replace(
                '|/([^/?]+)(\?.*)?$|',
                '/$1_w' . $workerIndex . '$2',
                $env['WORDPRESS_DB_URL']
            ) ?? $env['WORDPRESS_DB_URL'];
        }

        $env['TEST_TMP_ROOT_DIR'] = $env['TEST_TMP_ROOT_DIR'] ?? 'var/tmp';
        $env['TEST_CACHE_DIR'] = $env['TEST_CACHE_DIR'] ?? 'var/tmp/_cache';
        $env['WPBROWSER_WORKER_ID'] = (string)$workerIndex;

        return $env;
    }

    /**
     * @param array<string,int>|null $portOverrides
     * @return array<int,string>
     */
    public static function overridesForWorker(int $workerIndex, ?array $portOverrides = null): array
    {
        $ports = self::resolvePorts($workerIndex, $portOverrides);
        $tokens = [];
        foreach (self::EXTENSION_PORTS as $fqcn => $portVar) {
            $tokens[] = '--override';
            $tokens[] = sprintf("extensions: config: '%s': port: %d", $fqcn, $ports[$portVar]);
        }
        return $tokens;
    }

    /**
     * @param array<string,int>|null $portOverrides
     * @return array<string,int>
     */
    private static function resolvePorts(int $workerIndex, ?array $portOverrides): array
    {
        $ports = [];
        foreach (self::BASE_PORTS as $name => $base) {
            $ports[$name] = $portOverrides[$name] ?? ($base + ($workerIndex * self::PORT_STRIDE));
        }
        return $ports;
    }

    /**
     * @param array<string,mixed> $baseEnv
     * @return array<string,string>
     */
    private static function stringifyEnv(array $baseEnv): array
    {
        $out = [];
        foreach ($baseEnv as $k => $v) {
            if (is_scalar($v) || $v === null) {
                $out[(string)$k] = (string)($v ?? '');
            }
        }
        return $out;
    }
}
