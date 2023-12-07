<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use Closure;
use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Database\DatabaseInterface;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Version;

interface InstallationStateInterface
{
    public const SINGLE_SITE = 0;
    public const MULTISITE_SUBFOLDER = 1;
    public const MULTISITE_SUBDOMAIN = 2;

    public function getWpRootDir(string $path = ''): string;

    public function isMultisite(): bool;

    public function isSubdomainMultisite(): bool;

    public function scaffold(string $version = 'latest'): InstallationStateInterface;

    public function configure(
        DatabaseInterface $db,
        int $multisite = self::SINGLE_SITE,
        ?ConfigurationData $configurationData = null
    ): InstallationStateInterface;

    public function getAuthKey(): string;

    public function getSecureAuthKey(): string;

    public function getLoggedInKey(): string;

    public function getNonceKey(): string;

    public function getAuthSalt(): string;

    public function getSecureAuthSalt(): string;

    public function getLoggedInSalt(): string;

    public function getNonceSalt(): string;

    public function getTablePrefix(): string;

    public function install(
        string $url,
        string $adminUser,
        string $adminPassword,
        string $adminEmail,
        string $title
    ): InstallationStateInterface;

    public function convertToMultisite(bool $subdomainInstall = false): InstallationStateInterface;

    public function getWpConfigPath(): string;

    public function isConfigured(): bool;

    /**
     * @return array{
     *     AUTH_KEY: string,
     *     SECURE_AUTH_KEY: string,
     *     LOGGED_IN_KEY: string,
     *     NONCE_KEY: string,
     *     AUTH_SALT: string,
     *     SECURE_AUTH_SALT: string,
     *     LOGGED_IN_SALT: string,
     *     NONCE_SALT: string,
     * }
     */
    public function getSalts(): array;

    public function getVersion(): Version;

    /**
     * @return mixed
     */
    public function getConstant(string $constant);

    public function getDb(): DatabaseInterface;

    /**
     * @return array<string,mixed>
     */
    public function getConstants(): array;

    /**
     * @return array<string,mixed>
     */
    public function getGlobals(): array;

    public function getPluginsDir(string $path = ''): string;

    public function getMuPluginsDir(string $path = ''): string;

    public function getThemesDir(string $path = ''): string;

    public function getContentDir(string $path = ''): string;

    /**
     * @param mixed $value
     */
    public function updateOption(string $option, $value): int;

    /**
     * @return mixed
     */
    public function executeClosureInWordPress(Closure $closure);

    public function setDb(DatabaseInterface $db): InstallationStateInterface;
}
