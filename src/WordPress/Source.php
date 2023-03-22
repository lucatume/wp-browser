<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Utils\Download;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Zip;

class Source
{
    public static function getForVersion(string $version = 'latest'): string
    {
        $cacheDir = Env::get('TEST_CACHE_DIR', codecept_output_dir('_cache'));
        $sourceDir = $cacheDir . '/wordpress/' . $version;

        if (!is_dir($sourceDir) || !is_file($sourceDir . '/wp-config-sample.php')) {
            FS::mkdirp($sourceDir);
            $zipFile = $cacheDir . "/wordpress-$version.zip";

            if (!is_file($zipFile)) {
                $zipFile = Download::fileFromUrl(self::getWPDownloadUrl($version), $zipFile);
            }

            Zip::extractTo($zipFile, dirname($sourceDir));
            FS::rrmdir($sourceDir);
            rename(dirname($sourceDir) . '/wordpress', $sourceDir);

            if (!unlink($zipFile)) {
                throw new InstallationException(
                    "Could not delete $zipFile.",
                    InstallationException::DELETE_ERROR
                );
            }
        }

        return $sourceDir;
    }

    public static function getWPDownloadUrl(string $version): string
    {
        return match ($version) {
            'latest' => 'https://wordpress.org/latest.zip',
            'nightly' => 'https://wordpress.org/nightly-builds/wordpress-latest.zip',
            default => "https://wordpress.org/wordpress-{$version}.zip",
        };
    }
}
