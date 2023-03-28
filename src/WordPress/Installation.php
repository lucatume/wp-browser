<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\CodeExecution\CodeExecutionFactory;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequestClosureFactory;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequestFactory;
use lucatume\WPBrowser\WordPress\InstallationState\EmptyDir;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationStateInterface;
use lucatume\WPBrowser\WordPress\Traits\WordPressChecks;

class Installation
{
    use WordPressChecks;

    private ?Db $db = null;
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
    private ?Version $version = null;
    private string $wpRootDir;
    private ?string $wpConfigFilePath = null;
    private ?bool $isMultisite = null;
    private CodeExecutionFactory $codeExecutionFactory;
    private string $url;
    private ?WPConfigFile $wpConfigFileReader = null;
    private InstallationState\InstallationStateInterface $installationState;

    /**
     * @throws InstallationException
     */
    public function __construct(
        string $wpRootDir,
        ?Db $db = null,
    ) {
        $this->wpRootDir = $this->checkWpRootDir($wpRootDir);
        $this->installationState = $this->setInstallationState();
        $this->db = $db;
    }

    /**
     * @throws InstallationException
     */
    public static function scaffold(string $wpRootDir, string $version = 'latest'): self
    {
        $emptyDir = new EmptyDir($wpRootDir);
        $emptyDir->scaffold($version);

        return new self($wpRootDir);
    }

    public function configure(
        Db $db,
        ?int $multisite = InstallationStateInterface::SINGLE_SITE,
        ?ConfigurationData $configurationData = null
    ): self {
        $this->installationState = $this->installationState->configure($db, $multisite, $configurationData);

        return $this;
    }

    public function convertToMultisite($subdomainInstall = false): self
    {
        $this->installationState = $this->installationState->convertToMultisite($subdomainInstall);

        return $this;
    }


    public function getAuthKey(): string
    {
        return $this->installationState->getAuthKey();
    }

    public function getSecureAuthKey(): string
    {
        return $this->installationState->getSecureAuthKey();
    }

    public function getLoggedInKey(): string
    {
        return $this->installationState->getLoggedInKey();
    }

    public function getNonceKey(): string
    {
        return $this->installationState->getNonceKey();
    }

    public function getAuthSalt(): string
    {
        return $this->installationState->getAuthSalt();
    }

    public function getSecureAuthSalt(): string
    {
        return $this->installationState->getSecureAuthSalt();
    }

    public function getLoggedInSalt(): string
    {
        return $this->installationState->getLoggedInSalt();
    }

    public function getNonceSalt(): string
    {
        return $this->installationState->getNonceSalt();
    }

    public function getRootDir(): string
    {
        return $this->installationState->getWpRootDir();
    }

    public function getVersion(): ?Version
    {
        return $this->installationState->getVersion();
    }

    public function isMultisite(): bool
    {
        return $this->installationState->isMultisite();
    }

    public function getDb(): ?Db
    {
        return $this->installationState->getDb();
    }

    public function getWpRootDir(?string $path = null): string
    {
        return empty($path) ? $this->wpRootDir : $this->wpRootDir . FS::unleadslashit($path);
    }

    /**
     * @throws DbException
     * @throws InstallationException
     * @throws ProcessException
     */
    private function setInstallationState(): InstallationState\InstallationStateInterface
    {
        if (!is_file($this->wpRootDir . '/wp-load.php')) {
            return new InstallationState\EmptyDir($this->wpRootDir);
        }

        $wpConfigFilePath = $this->findWpConfigFilePath($this->wpRootDir);

        if (!$wpConfigFilePath) {
            return new InstallationState\Scaffolded($this->wpRootDir);
        }

        $db = $this->db ?? Db::fromWpConfigFile(new WPConfigFile($this->wpRootDir, $wpConfigFilePath));

        $installationState = new InstallationState\Configured($this->wpRootDir, $wpConfigFilePath, $db);
        $multisite = $installationState->isMultisite();

        if ($this->db === null || !$this->isInstalled($multisite)) {
            return $installationState;
        }

        return $multisite ?
            new InstallationState\Multisite($this->wpRootDir, $wpConfigFilePath, $this->db)
            : new InstallationState\Single($this->wpRootDir, $wpConfigFilePath, $this->db);
    }

    public function isEmpty(): bool
    {
        return $this->installationState instanceof InstallationState\EmptyDir;
    }

    public function install(
        string $url,
        string $adminUser,
        string $adminPassword,
        string $adminEmail,
        string $title
    ): self {
        $this->installationState = $this->installationState->install(
            $url,
            $adminUser,
            $adminPassword,
            $adminEmail,
            $title
        );

        return $this;
    }

    public function getState(): InstallationState\InstallationStateInterface
    {
        return $this->installationState;
    }

    public function isConfigured(): bool
    {
        return $this->installationState->isConfigured();
    }

    public function getSalts(): array
    {
        return $this->installationState->getSalts();
    }

    public function report(array $checkKeys = null): array
    {
        $map = [
            'rootDir' => fn() => $this->installationState->getWpRootDir(),
            'version' => fn() => $this->installationState->getVersion()->toArray(),
            'constants' => fn() => $this->installationState->getConstants(),
            'globals' => fn() => $this->installationState->getGlobals()
        ];

        $checksMap = $checkKeys === null ? $map : array_intersect_key($map, array_flip($checkKeys));

        foreach ($checksMap as &$value) {
            $value = $value();
        }

        return $checksMap;
    }

    public function getPluginDir(string $path = ''): string
    {
        return $this->installationState->getPluginDir($path);
    }

    public function updateOption(string $option, array $value): int
    {
        return $this->installationState->updateOption($option, $value);
    }
}
