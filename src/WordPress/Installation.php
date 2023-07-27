<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\Database\DatabaseInterface;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\InstallationState\EmptyDir;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationChecks;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationStateInterface;
use lucatume\WPBrowser\WordPress\Traits\WordPressChecks;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Throwable;

class Installation
{
    use WordPressChecks;
    use InstallationChecks;

    public const SQLITE_PLUGIN = __DIR__ . '/../../includes/sqlite-database-integration';

    /**
     * @var array<string>
     */
    private static array $scaffoldedInstallations = [];
    private ?DatabaseInterface $db;
    private string $wpRootDir;
    private InstallationState\InstallationStateInterface $installationState;

    /**
     * @throws DbException
     * @throws InstallationException
     * @throws ProcessException
     * @throws Throwable
     * @throws WorkerException
     * @throws WpConfigFileException
     */
    public function __construct(
        string $wpRootDir,
        ?DatabaseInterface $db = null,
    ) {
        $this->wpRootDir = $this->checkWpRootDir($wpRootDir);
        $this->db = $db;
        $this->installationState = $this->setInstallationState();
    }

    /**
     * @throws DbException
     * @throws InstallationException
     * @throws ProcessException
     * @throws Throwable
     * @throws WorkerException
     * @throws WpConfigFileException
     */
    public static function scaffold(string $wpRootDir, string $version = 'latest'): self
    {
        $emptyDir = new EmptyDir($wpRootDir);
        $emptyDir->scaffold($version);

        self::$scaffoldedInstallations[] = $wpRootDir;

        return new self($wpRootDir);
    }

    /**
     * @return array<string>
     */
    public static function getCleanScaffoldedInstallations(): array
    {
        $installations = self::$scaffoldedInstallations;
        self::$scaffoldedInstallations = [];

        return $installations;
    }

    /**
     * @throws InstallationException
     */
    public static function placeSqliteMuPlugin(string $getMuPluginsDir, string $contentDir): void
    {
        if (!(
            FS::mkdirp($getMuPluginsDir)
            && FS::recurseCopy(self::SQLITE_PLUGIN, $getMuPluginsDir . '/sqlite-database-integration')
        )) {
            throw new InstallationException(
                "Could not copy the SQLite mu-plugin file to $getMuPluginsDir.",
                InstallationException::SQLITE_PLUGIN_COPY_FAILED
            );
        }

        $pluginDirPathname = $getMuPluginsDir . '/sqlite-database-integration';
        $dbCopyPathname = $pluginDirPathname . '/db.copy';
        $dbCopyContents = file_get_contents($dbCopyPathname);

        if (empty($dbCopyContents)) {
            throw new InstallationException(
                "Could not read the SQLite db.copy file at $dbCopyPathname.",
                InstallationException::SQLITE_PLUGIN_DB_COPY_READ_FAILED
            );
        }

        $updatedContents = str_replace(
            [
                '{SQLITE_IMPLEMENTATION_FOLDER_PATH}',
                '{SQLITE_PLUGIN}'
            ],
            [
                $pluginDirPathname,
                'sqlite-database-integration/load.php'
            ],
            $dbCopyContents
        );

        if (!file_put_contents($contentDir . '/db.php', $updatedContents, LOCK_EX)) {
            throw new InstallationException(
                "Could not write the SQLite db.php file at $contentDir.",
                InstallationException::SQLITE_DROPIN_COPY_FAILED
            );
        }
    }

    public function configure(
        DatabaseInterface $db,
        int $multisite = InstallationStateInterface::SINGLE_SITE,
        ConfigurationData $configurationData = null
    ): self {
        $this->installationState = $this->installationState->configure($db, $multisite, $configurationData);

        return $this;
    }

    public function convertToMultisite(bool $subdomainInstall = false): self
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

    public function getDb(): ?DatabaseInterface
    {
        return $this->db ?? $this->installationState->getDb();
    }

    public function getWpRootDir(?string $path = null): string
    {
        return empty($path) ? $this->wpRootDir : $this->wpRootDir . FS::unleadslashit($path);
    }

    /**
     * @throws DbException
     * @throws InstallationException
     * @throws ProcessException
     * @throws WpConfigFileException
     * @throws Throwable
     * @throws WorkerException
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

        $installationState = new InstallationState\Configured($this->wpRootDir, $wpConfigFilePath);
        $multisite = $installationState->isMultisite();

        if ($this->db === null || !$this->isInstalled($multisite)) {
            return $installationState;
        }

        return $multisite ?
            new InstallationState\Multisite($this->wpRootDir, $wpConfigFilePath)
            : new InstallationState\Single($this->wpRootDir, $wpConfigFilePath);
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

    /**
     * @return array{
     *     AUTH_KEY: string,
     *     SECURE_AUTH_KEY: string,
     *     LOGGED_IN_KEY: string,
     *     NONCE_KEY: string,
     *     AUTH_SALT: string,
     *     SECURE_AUTH_SALT: string,
     *     LOGGED_IN_SALT: string,
     *     NONCE_SALT: string
     * }
     */
    public function getSalts(): array
    {
        return $this->installationState->getSalts();
    }

    /**
     * @param ?array<string> $checkKeys
     *
     * @return array<string, mixed>
     */
    public function report(array $checkKeys = null): array
    {
        $map = [
            'rootDir' => fn(): string => $this->installationState->getWpRootDir(),
            'version' => fn(): array => $this->installationState->getVersion()->toArray(),
            'constants' => fn(): array => $this->installationState->getConstants(),
            'globals' => fn(): array => $this->installationState->getGlobals()
        ];

        $checksMap = $checkKeys === null ? $map : array_intersect_key($map, array_flip($checkKeys));

        foreach ($checksMap as &$value) {
            $value = $value();
        }

        return $checksMap;
    }

    public function getPluginsDir(string $path = ''): string
    {
        return $this->installationState->getPluginsDir($path);
    }

    public function updateOption(string $option, mixed $value): int
    {
        return $this->installationState->updateOption($option, $value);
    }

    public function getThemesDir(string $path = ''): string
    {
        return $this->installationState->getThemesDir($path);
    }

    public function getWpConfigFilePath(): string
    {
        return $this->installationState->getWpConfigPath();
    }

    public function getContentDir(string $path = ''): string
    {
        return $this->installationState->getContentDir($path);
    }

    /**
     * @param string[] $command
     *
     * @throws ProcessFailedException
     */
    public function runWpCliCommandOrThrow(array $command): Process
    {
        return (new CliProcess($command, $this->getRootDir()))->mustRun();
    }

    public function usesSqlite(): bool
    {
        return $this->installationState->getDb() instanceof SQLiteDatabase;
    }

    public function usesMysql(): bool
    {
        return $this->installationState->getDb() instanceof MySQLDatabase;
    }
}
