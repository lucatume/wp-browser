<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Utils\Download;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Zip;
use lucatume\WPBrowser\Utils\Env;

class Source
{
    public static function getForVersion(string $version = 'latest'): string
    {
        $envSourceDir = Env::get('WPBROWSER_WORDPRESS_SOURCE_DIR', null);

        if (is_string($envSourceDir) && is_dir($envSourceDir . DIRECTORY_SEPARATOR . $version)) {
            return $envSourceDir . DIRECTORY_SEPARATOR . $version;
        }

        $cacheDir = FS::cacheDir();
        $versionsCacheDir = $cacheDir . '/wordpress/';
        $sourceDir = $versionsCacheDir . $version;

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
        switch ($version) {
            case 'latest':
                return 'https://wordpress.org/latest.zip';
            case 'nightly':
                return 'https://wordpress.org/nightly-builds/wordpress-latest.zip';
            default:
                return "https://wordpress.org/wordpress-{$version}.zip";
        }
    }

    public static function getWordPressVersionsCacheDir(): string
    {
        $cacheDir = FS::cacheDir();
        return $cacheDir . '/wordpress/';
    }
}
