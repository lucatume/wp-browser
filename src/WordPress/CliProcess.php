<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Utils\Download;
use lucatume\WPBrowser\Utils\Filesystem;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class CliProcess extends Process
{
    private const WP_CLI_PHAR_URL = 'https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar';

    /**
     * @param array<string> $command
     * @param array<string,mixed>|null $env
     *
     * @throws RuntimeException
     */
    public function __construct(
        array $command,
        ?string $cwd = null,
        ?array $env = null,
        $input = null,
        ?float $timeout = 60,
        ?string $bin = null
    ) {
        if ($bin === null) {
            $wpCliPhar = self::getWpCliPharPath();
        } else {
            try {
                $binAbsolutePath = Filesystem::resolvePath($bin);
            } catch (\Exception $e) {
                throw new InvalidArgumentException(
                    'Failed to resolve custom binary path: does it exist?',
                    $e->getCode(),
                    $e
                );
            }

            if ($binAbsolutePath === false || !is_executable($binAbsolutePath)) {
                throw new InvalidArgumentException(
                    'WPCLI bin not found or not executable: ' . $binAbsolutePath
                );
            }
            $wpCliPhar = $binAbsolutePath;
        }

        array_unshift($command, PHP_BINARY, $wpCliPhar);
        parent::__construct($command, $cwd, $env, $input, $timeout);
    }

    /**
     * @param array<string,mixed>|null $env
     * @param mixed $input
     * @return static
     */
    public static function fromShellCommandline(
        string $command,
        ?string $cwd = null,
        ?array $env = null,
        $input = null,
        ?float $timeout = 60
    ) {
        $command = implode(' ', [
            escapeshellarg(PHP_BINARY),
            escapeshellarg(self::getWpCliPharPath()),
            $command
        ]);
        return parent::fromShellCommandline(
            $command,
            $cwd,
            $env,
            $input,
            $timeout
        );
    }

    /**
     * @throws RuntimeException
     */
    private static function getWpCliPharPath(): string
    {
        $binDir = codecept_output_dir('bin');
        FS::mkdirp($binDir, [], 0755);
        $wpCliPhar = $binDir . '/wp-cli.phar';

        if (is_file($wpCliPhar)) {
            return $wpCliPhar;
        }

        return self::downloadWpCliPhar($wpCliPhar);
    }

    private static function downloadWpCliPhar(string $wpCliPhar): string
    {
        return Download::fileFromUrl(self::WP_CLI_PHAR_URL, $wpCliPhar);
    }

    public static function getWpCliPharPathname(): string
    {
        return codecept_output_dir('bin/wp-cli.phar');
    }
}
