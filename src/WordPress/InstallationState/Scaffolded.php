<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use Closure;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Database\DatabaseInterface;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\Traits\WordPressChecks;
use lucatume\WPBrowser\WordPress\Version;
use lucatume\WPBrowser\WordPress\WPConfigFile;
use lucatume\WPBrowser\WordPress\WpConfigFileException;
use lucatume\WPBrowser\WordPress\WpConfigFileGenerator;
use Throwable;

class Scaffolded implements InstallationStateInterface
{
    use WordPressChecks;
    use ScaffoldedStateTrait;

    /**
     * @var string
     */
    private $wpRootDir;

    /**
     * @throws InstallationException
     */
    public function __construct(string $wpRootDir)
    {
        $this->wpRootDir = $this->checkWPRootDir($wpRootDir);

        if (!is_file($this->wpRootDir . 'wp-load.php')) {
            throw new InstallationException(
                'The WordPress installation is not scaffolded.',
                InstallationException::STATE_EMPTY
            );
        }

        if ($this->findWpConfigFilePath($this->wpRootDir)) {
            throw new InstallationException(
                'The WordPress installation is already configured.',
                InstallationException::STATE_CONFIGURED
            );
        }

        $this->version = new Version($this->wpRootDir);
    }

    /**
     * @throws InstallationException
     */
    public function isMultisite(): bool
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    public function getWpRootDir(string $path = ''): string
    {
        return $path ? $this->wpRootDir . ltrim($path, '\\/') : $this->wpRootDir;
    }

    /**
     * @throws DbException
     * @throws InstallationException
     * @throws ProcessException
     * @throws Throwable
     * @throws WpConfigFileException
     */
    public function configure(
        DatabaseInterface $db,
        int $multisite = InstallationStateInterface::SINGLE_SITE,
        ?ConfigurationData $configurationData = null
    ): InstallationStateInterface {
        $wpConfigFilePath = $this->wpRootDir . 'wp-config.php';
        $configurationData = $configurationData ?? new ConfigurationData();
        codecept_debug("Creating the {$wpConfigFilePath} file ...");
        $wpConfigFileContents = (new WpConfigFileGenerator($this->wpRootDir))->produce(
            $db,
            $configurationData,
            $multisite
        );
        if (!file_put_contents($wpConfigFilePath, $wpConfigFileContents, LOCK_EX)) {
            throw new InstallationException("Could not write to $wpConfigFilePath.");
        }
        $configured= new Configured($this->wpRootDir, $wpConfigFilePath);

        if ($db instanceof SQLiteDatabase) {
            Installation::placeSqliteMuPlugin($configured->getMuPluginsDir(), $configured->getContentDir());
        }

        return $configured;
    }

    /**
     * @throws InstallationException
     */
    public function getAuthKey(): string
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     */
    public function getSecureAuthKey(): string
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     */
    public function getLoggedInKey(): string
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     */
    public function getNonceKey(): string
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     */
    public function getAuthSalt(): string
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     */
    public function getSecureAuthSalt(): string
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     */
    public function getLoggedInSalt(): string
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     */
    public function getNonceSalt(): string
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     */
    public function getTablePrefix(): string
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
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
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     */
    public function convertToMultisite(bool $subdomainInstall = false): InstallationStateInterface
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     */
    public function scaffold(string $version = 'latest'): InstallationStateInterface
    {
        throw new InstallationException(
            'The WordPress installation has already been scaffolded.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     */
    public function getWpConfigPath(): string
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
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
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     */
    public function isSubdomainMultisite(): bool
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     * @return mixed
     */
    public function getConstant(string $constant)
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     */
    public function getDb(): DatabaseInterface
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @return array{ABSPATH: string}
     */
    public function getConstants(): array
    {
        return [
            'ABSPATH' => $this->wpRootDir,
        ];
    }

    /**
     * @return array{table_prefix: string}
     */
    public function getGlobals(): array
    {
        return ['table_prefix' => 'wp_'];
    }

    public function getContentDir(string $path = ''): string
    {
        return $this->wpRootDir . 'wp-content/' . ltrim($path, '\\/');
    }

    public function getPluginsDir(string $path = ''): string
    {
        return $this->getContentDir('plugins/' . ltrim($path, '\\/'));
    }

    public function getMuPluginsDir(string $path = ''): string
    {
        return $this->getContentDir('mu-plugins/' . ltrim($path, '\\/'));
    }

    public function getThemesDir(string $path = ''): string
    {
        return $this->getContentDir('themes/' . ltrim($path, '\\/'));
    }

    /**
     * @throws InstallationException
     * @param mixed $value
     */
    public function updateOption(string $option, $value): int
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }


    /**
     * @throws InstallationException
     * @return mixed
     */
    public function executeClosureInWordPress(Closure $closure)
    {
        throw new InstallationException(
            'The WordPress installation has not been configured yet.',
            InstallationException::STATE_SCAFFOLDED
        );
    }

    /**
     * @throws InstallationException
     */
    public function setDb(DatabaseInterface $db): InstallationStateInterface
    {
        throw new InstallationException(
            'The WordPress installation has already been configured.',
            InstallationException::STATE_SCAFFOLDED
        );
    }
}
