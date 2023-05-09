<?php

namespace lucatume\WPBrowser\WordPress\Traits;

use Exception;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\InstallationException;

trait WordPressChecks
{
    /**
     * @throws InstallationException
     */
    private function checkWPRootDir(string $wpRootDir): string
    {
        try {
            $wpRootDir = FS::untrailslashit((string)FS::resolvePath($wpRootDir)) . '/';
        } catch (Exception $e) {
            throw new InstallationException("{$wpRootDir} does not exist.",
                InstallationException::ROOT_DIR_NOT_FOUND, $e);
        }

        if (!(is_dir($wpRootDir) && is_readable($wpRootDir) && is_writable($wpRootDir))) {
            throw new InstallationException("{$wpRootDir} is not a readable and writable directory.",
                InstallationException::ROOT_DIR_NOT_RW);
        }

        return $wpRootDir;
    }


    private function findWpConfigFilePath(string $rootDir): string|false
    {
        $wpConfigFile = rtrim($rootDir, '\\/') . '/wp-config.php';

        if (!is_file($wpConfigFile)) {
            // wp-config.php not found in root dir, try one level up.
            $wpConfigFile = dirname($rootDir) . '/wp-config.php';
        }

        return is_file($wpConfigFile) ? $wpConfigFile : false;
    }
}
