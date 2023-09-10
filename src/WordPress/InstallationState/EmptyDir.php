<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use Closure;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Database\DatabaseInterface;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\Source;
use lucatume\WPBrowser\WordPress\Traits\WordPressChecks;
use lucatume\WPBrowser\WordPress\Version;

class EmptyDir implements InstallationStateInterface
{
    use WordPressChecks;

    private string $wpRootDir;

    /**
     * @throws InstallationException
     */
    public function __construct(string $wpRootDir)
    {
        $this->wpRootDir = $this->checkWPRootDir($wpRootDir);
        if (is_file($this->wpRootDir . '/wp-load.php')) {
            throw new InstallationException(
                'The WordPress installation is not empty.',
                InstallationException::STATE_SCAFFOLDED
            );
        }
    }

    public function getWpRootDir(string $path = ''): string
    {
        return $this->wpRootDir;
    }

    /**
     * @throws InstallationException
     */
    public function isMultisite(): bool
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function configure(
        DatabaseInterface $db,
        int $multisite = InstallationStateInterface::SINGLE_SITE,
        ?ConfigurationData $configurationData = null
    ): InstallationStateInterface {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getAuthKey(): string
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getSecureAuthKey(): string
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getLoggedInKey(): string
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getNonceKey(): string
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getAuthSalt(): string
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getSecureAuthSalt(): string
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getLoggedInSalt(): string
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getNonceSalt(): string
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getTablePrefix(): string
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function install(
        string $url,
        string $adminUser,
        string $adminPassword,
        string $adminEmail,
        string $title
    ): InstallationStateInterface {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function convertToMultisite(bool $subdomainInstall = false): InstallationStateInterface
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function scaffold(string $version = 'latest'): InstallationStateInterface
    {
        $sourceDir = Source::getForVersion($version);
        codecept_debug(sprintf("Copying %s to %s ... ", $sourceDir, $this->wpRootDir));
        if (!FS::recurseCopy($sourceDir, $this->wpRootDir)) {
            throw new InstallationException(
                "Could not copy WordPress files to $this->wpRootDir.",
                InstallationException::WRITE_ERROR
            );
        }

        return new Scaffolded($this->wpRootDir);
    }

    /**
     * @throws InstallationException
     */
    public function getWpConfigPath(): string
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    public function isConfigured(): bool
    {
        return false;
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
     *     NONCE_SALT: string,
     * }
     * @throws InstallationException
     */
    public function getSalts(): array
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getVersion(): Version
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function isSubdomainMultisite(): bool
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getConstant(string $constant): mixed
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getDb(): DatabaseInterface
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @return array<string,mixed>
     * @throws InstallationException
     */
    public function getConstants(): array
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @return array<string,mixed>
     * @throws InstallationException
     */
    public function getGlobals(): array
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getPluginsDir(string $path = ''): string
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getMuPluginsDir(string $path = ''): string
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function updateOption(string $option, mixed $value): int
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getThemesDir(string $path = ''): string
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function getContentDir(string $path = ''): string
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function executeClosureInWordPress(Closure $closure): mixed
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }

    /**
     * @throws InstallationException
     */
    public function setDb(DatabaseInterface $db): InstallationStateInterface
    {
        throw new InstallationException(
            'The WordPress installation is empty.',
            InstallationException::STATE_EMPTY
        );
    }
}
