<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Utils\Download;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use Symfony\Component\Process\Process;

class CliProcess extends Process
{
    /**
     * @throws RuntimeException
     */
    public function __construct(
        array $command,
        ?string $cwd = null,
        ?array $env = null,
        $input = null,
        ?float $timeout = 60
    ) {
        $wpCliPhar = self::getWpCliPharPath();
        array_unshift($command, PHP_BINARY, $wpCliPhar);
        parent::__construct($command, $cwd, $env, $input, $timeout);
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
        return Download::fileFromUrl(
            'https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar',
            $wpCliPhar
        );
    }
}
