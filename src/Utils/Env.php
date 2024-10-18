<?php

declare(strict_types=1);

/**
 * Environment and Operating System related functions.
 *
 * @package lucatume\WPBrowser
 */

namespace lucatume\WPBrowser\Utils;

use InvalidArgumentException;
use RuntimeException;

class Env
{
    /**
     * Returns a closure to get the value of an environment variable, loading a specific env file first.
     *
     * @param string $file The name of the environment file to load.
     *
     * @return array<string,string|false> The map of the read environment variables.
     *
     * @throws RuntimeException If the env file does not exist or is not readable.
     */
    public static function envFile(string $file): array
    {
        if (!(is_file($file))) {
            throw new InvalidArgumentException('File ' . $file . ' does not exist.');
        }
        if (!is_readable($file)) {
            throw new InvalidArgumentException('File ' . $file . ' is not readable.');
        }

        $envFileContents = file_get_contents($file);

        if ($envFileContents === false) {
            throw new InvalidArgumentException('Could not read ' . $file . ' contents.');
        }

        $envFileContents = str_replace("\r\n", "\n", $envFileContents);

        $pattern = '/^(?<key>.*?)=("(?<q_value>.*)(?<!\\\\)"|(?<value>.*?))([\\s]*#.*)*$/ui';

        $vars = array_reduce(
            array_filter(explode("\n", $envFileContents)),
            static function (array $lines, $line) use ($pattern): array {
                if (strncmp($line, '#', strlen('#')) === 0) {
                    return $lines;
                }

                if (!preg_match($pattern, $line, $m)) {
                    return $lines;
                }

                if (!empty($m['q_value'])) {
                    $value = $m['q_value'];
                } else {
                    $value = isset($m['value']) ? trim($m['value'], ' \'"') : '';
                }
                // Replace escaped double quotes.
                $value = str_replace('\\"', '"', $value);

                $lines[$m['key']] = $value;

                return $lines;
            },
            []
        );

        return $vars;
    }

    /**
     * Returns the pretty, human-readable name of the current Operating System.
     *
     * @return string The human-readable name of the OS PHP is running on. One of `Windows`, `Linux`, 'macOS`,
     *                'Solaris`,
     *                `BSD` or`Unknown`.
     */
    public static function os(): string
    {
        $osSlug = strtolower(substr(PHP_OS, 0, 3));

        $map = [
            'win' => 'Windows',
            'lin' => 'Linux',
            'dar' => 'macOS',
            'bsd' => 'BSD',
            'sol' => 'Solaris'
        ];

        return $map[$osSlug] ?? 'Unknown';
    }

    /**
     * Loads a Map of environment variables into `getenv()`, `$_ENV` and `$_SERVER`.
     *
     * @param array<string,string|false> $map The map of environment variables to load.
     * @param bool $overwrite Whether to overwrite the existing env vars or not.
     *
     * @see envFile() For the function to load to generate a Map from an environment file.
     *
     */
    public static function loadEnvMap(array $map, bool $overwrite = true): void
    {
        if (empty($_SERVER)) {
            $_SERVER = [];
        }

        if (empty($_ENV)) {
            $_ENV = [];
        }

        $load = $map;

        if (!$overwrite) {
            $load = array_filter($map, static function ($key): bool {
                return !isset($_ENV[$key]);
            }, ARRAY_FILTER_USE_KEY);
        }

        foreach ($load as $key => $value) {
            putenv("{$key}={$value}");
            $_SERVER[$key] = $value;
            $_ENV[$key] = $value;
        }
    }

    /**
     * @return string|false|null
     */
    public static function get(string $key, string $default = null)
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? (getenv($key) ?: $default);
    }
}
