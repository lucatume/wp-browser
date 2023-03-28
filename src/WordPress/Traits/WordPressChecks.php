<?php

namespace lucatume\WPBrowser\WordPress\Traits;

use Exception;
use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\Utils\Arr;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Strings;
use lucatume\WPBrowser\WordPress\CodeExecution\CodeExecutionFactory;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\InstallationException;
use Throwable;

trait WordPressChecks
{
    /**
     * @throws InstallationException
     */
    protected function validateInstallationParameters(
        string $url,
        string $adminUser,
        string $adminPassword,
        string $adminEmail,
        string $title
    ): void {
        /** @noinspection BypassedUrlValidationInspection */
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InstallationException(
                "The URL $url is not a valid URL.",
                InstallationException::INVALID_URL
            );
        }

        $safeAdminUser = Strings::slug($adminUser);
        if ($adminUser === '' || $safeAdminUser !== $adminUser) {
            throw new InstallationException(
                "The admin user $adminUser is not a valid user name.",
                InstallationException::INVALID_ADMIN_USERNAME
            );
        }

        if (empty($adminPassword)) {
            throw new InstallationException(
                "The admin password is empty.",
                InstallationException::INVALID_ADMIN_PASSWORD
            );
        }

        if (!filter_var($adminEmail, FILTER_SANITIZE_EMAIL)) {
            throw new InstallationException(
                "The admin email $adminEmail is not a valid email address.",
                InstallationException::INVALID_ADMIN_EMAIL
            );
        }

        if (empty($title)) {
            throw new InstallationException(
                "The site title is empty.",
                InstallationException::INVALID_TITLE
            );
        }
    }

    /**
     * @throws Throwable
     * @throws DbException
     * @throws WorkerException
     * @throws ProcessException
     */
    protected function isInstalled(bool $multisite): bool
    {
        if (!($this->wpRootDir && $this->db->exists())) {
            return false;
        }

        $siteurl = $this->db->getOption('siteurl');

        if (!(is_string($siteurl) && $siteurl !== '')) {
            return false;
        }

        $host = parse_url($siteurl, PHP_URL_HOST);

        if (!$host) {
            return false;
        }

        $codeExecutionFactory = new CodeExecutionFactory($this->wpRootDir, $host);
        $result = Loop::executeClosure($codeExecutionFactory->toCheckIfWpIsInstalled($multisite));
        $returnValue = $result->getReturnValue();

        return (Arr::firstFrom($returnValue, false) === true);
    }

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
