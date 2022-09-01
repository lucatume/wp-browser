<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Utils\Download;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Password;
use lucatume\WPBrowser\Utils\Process;
use lucatume\WPBrowser\Utils\Zip;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequestFactory;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequestClosureFactory;

class Installation
{
    private ?Db $db;
    private FileRequestFactory $fileRequestFactory;
    private FileRequestClosureFactory $requestClosuresFactory;
    private string $adminEmail;
    private string $adminPassword;
    private string $adminUser;
    private string $authKey;
    private string $authSalt;
    private string $loggedInKey;
    private string $loggedInSalt;
    private string $nonceKey;
    private string $nonceSalt;
    private string $secureAuthKey;
    private string $secureAuthSalt;
    private string $title;
    private string $version;
    private string $wpRootFolder;

    public function __construct(string $wpRootFolder, string $version, ?Db $db = null)
    {
        if (!is_dir($wpRootFolder) && is_readable($wpRootFolder) && is_writable($wpRootFolder)) {
            throw new InstallationException("{$wpRootFolder} is not an existing, readable and writable folder.");
        }

        $this->wpRootFolder = $wpRootFolder;
        $this->db = $db;
        $this->version = $version;
        $this->authKey = Password::salt(64);
        $this->secureAuthKey = Password::salt(64);
        $this->loggedInKey = Password::salt(64);
        $this->nonceKey = Password::salt(64);
        $this->authSalt = Password::salt(64);
        $this->secureAuthSalt = Password::salt(64);
        $this->loggedInSalt = Password::salt(64);
        $this->nonceSalt = Password::salt(64);
        $this->fileRequestFactory = new FileRequestFactory($this->wpRootFolder);
        $this->requestClosuresFactory = new FileRequestClosureFactory($this->fileRequestFactory);
        $this->title = 'WP Browser';
        $this->adminUser = 'admin';
        $this->adminPassword = Password::salt(12);
        $this->adminEmail = 'admin@installation.test';
    }

    public function up(): self
    {
        return $this->scaffold($this->version)
            ->configure()
            ->install();
    }

    public function scaffold(string $version = 'latest'): self
    {
        $source = $this->getWordPressSource($version);
        FS::recurseCopy($source, $this->wpRootFolder);
        return $this;
    }

    public function configure(): self
    {
        $wpConfigFile = $this->wpRootFolder . '/wp-config.php';

        if (is_file($wpConfigFile)) {
            return $this;
        }

        $wpConfigSampleFile = $this->wpRootFolder . '/wp-config-sample.php';

        $wpConfigFileContents = str_replace(
            [
                'database_name_here',
                'username_here',
                'password_here',
                'localhost',
                'wp_',
            ],
            [
                $this->db->getDbName(),
                $this->db->getDbUser(),
                $this->db->getDbPassword(),
                $this->db->getDbHost(),
                $this->db->getTablePrefix()
            ],
            file_get_contents($wpConfigSampleFile)
        );

        $wpConfigFileContents = preg_replace([
            '/put your unique phrase here/',
            '/put your unique phrase here/',
            '/put your unique phrase here/',
            '/put your unique phrase here/',
            '/put your unique phrase here/',
            '/put your unique phrase here/',
            '/put your unique phrase here/',
            '/put your unique phrase here/',
        ], [
            $this->getAuthKey(),
            $this->getSecureAuthKey(),
            $this->getLoggedInKey(),
            $this->getNonceKey(),
            $this->getAuthSalt(),
            $this->getSecureAuthSalt(),
            $this->getLoggedInSalt(),
            $this->getNonceSalt(),
        ], $wpConfigFileContents, 1);

        if (!file_put_contents($wpConfigFile, $wpConfigFileContents, LOCK_EX)) {
            throw new InstallationException("Could not write to {$wpConfigFile}");
        }

        return $this;
    }

    private function install(): self
    {
        $this->db->create();

        $request = $this->requestClosuresFactory->toInstall(
            $this->title,
            $this->adminUser,
            $this->adminPassword,
            $this->adminEmail
        );

        $result = Loop::executeClosure($request);

        if ($result->getExitCode() !== 0) {
            $returnValue = $result->getReturnValue();

            if ($returnValue instanceof \Throwable) {
                throw $returnValue;
            }

            $reason = $result->getStdoutBuffer();
            throw new InstallationException('Could not install WordPress: ' . $reason);
        }

        return $this;
    }

    private function getWordPressSource(string $version): string
    {
        $sourceDirectory = codecept_output_dir('_cache/wordpress/' . $version);

        if (!is_dir($sourceDirectory) || !is_file($sourceDirectory . '/wp-config-sample.php')) {
            FS::mkdirp($sourceDirectory);
            $zipFile = codecept_output_dir("_cache/wordpress-$version.zip");

            if (!is_file($zipFile)) {
                $zipFile = Download::fileFromUrl($this->getWPDownloadUrl($version), $zipFile);
            }

            Zip::extractTo($zipFile, dirname($sourceDirectory));
            FS::rrmdir($sourceDirectory);
            rename(dirname($sourceDirectory) . '/wordpress', $sourceDirectory);

            if (!unlink($zipFile)) {
                throw new InstallationException("Could not delete $zipFile.");
            }
        }

        return $sourceDirectory;
    }

    private function getWPDownloadUrl(string $version): string
    {
        return match ($version) {
            'latest' => 'https://wordpress.org/latest.zip',
            'nightly' => 'https://wordpress.org/nightly-builds/wordpress-latest.zip',
            default => "https://wordpress.org/wordpress-{$version}.zip",
        };
    }

    public function getAuthKey(): string
    {
        return $this->authKey;
    }

    public function getSecureAuthKey(): string
    {
        return $this->secureAuthKey;
    }

    public function getLoggedInKey(): string
    {
        return $this->loggedInKey;
    }

    public function getNonceKey(): string
    {
        return $this->nonceKey;
    }

    public function getAuthSalt(): string
    {
        return $this->authSalt;
    }

    public function getSecureAuthSalt(): string
    {
        return $this->secureAuthSalt;
    }

    public function getLoggedInSalt(): string
    {
        return $this->loggedInSalt;
    }

    public function getNonceSalt(): string
    {
        return $this->nonceSalt;
    }

    public function getRootDir(): string
    {
        return $this->wpRootFolder;
    }

    public function destroy(): void
    {
        $this->db->drop();
        FS::rrmdir($this->wpRootFolder);
    }
}
