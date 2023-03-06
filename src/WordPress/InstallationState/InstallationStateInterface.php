<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\Version;

interface InstallationStateInterface
{
    public const SINGLE_SITE = 0;
    public const MULTISITE_SUBFOLDER = 1;
    public const MULTISITE_SUBDOMAIN = 2;

    public function getWpRootDir(): string;

    public function isMultisite(): bool;

    public function isSubdomainMultisite(): bool;

    public function scaffold(string $version = 'latest'): InstallationStateInterface;

    public function configure(
        Db $db,
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

    public function getSalts(): array;

    public function getVersion(): Version;

    public function getConstant(string $constant): mixed;

    public function getDb(): Db;

    public function getConstants():array;

    public function getGlobals():array;

    public function getChecks():array;
}
