<?php


namespace lucatume\WPBrowser\WordPress\InstallationState;

use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\WordPress\Database\DatabaseInterface;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\WPConfigFile;
use lucatume\WPBrowser\WordPress\WpConfigFileException;
use Throwable;

trait ConfiguredStateTrait
{
    use InstallationChecks;

    private WPConfigFile $wpConfigFile;
    private string $wpRootDir;
    private DatabaseInterface $db;

    /**
     * @throws InstallationException
     */
    public function getAuthKey(): string
    {
        $constant = $this->wpConfigFile->getConstant('AUTH_KEY');
        if (!is_string($constant)) {
            throw new InstallationException(
                "Expected AUTH_KEY to be a string, got: " . gettype($constant),
                InstallationException::CONST_NOT_STRING
            );
        }
        return $constant;
    }

    /**
     * @throws InstallationException
     */
    public function getSecureAuthKey(): string
    {
        $constant = $this->wpConfigFile->getConstant('SECURE_AUTH_KEY');
        if (!is_string($constant)) {
            throw new InstallationException(
                "Expected SECURE_AUTH_KEY to be a string, got: " . gettype($constant),
                InstallationException::CONST_NOT_STRING
            );
        }
        return $constant;
    }

    /**
     * @throws InstallationException
     */
    public function getLoggedInKey(): string
    {
        $constant = $this->wpConfigFile->getConstant('LOGGED_IN_KEY');
        if (!is_string($constant)) {
            throw new InstallationException(
                "Expected LOGGED_IN_KEY to be a string, got: " . gettype($constant),
                InstallationException::CONST_NOT_STRING
            );
        }
        return $constant;
    }

    /**
     * @throws InstallationException
     */
    public function getNonceKey(): string
    {
        $constant = $this->wpConfigFile->getConstant('NONCE_KEY');
        if (!is_string($constant)) {
            throw new InstallationException(
                "Expected NONCE_KEY to be a string, got: " . gettype($constant),
                InstallationException::CONST_NOT_STRING
            );
        }
        return $constant;
    }

    /**
     * @throws InstallationException
     */
    public function getAuthSalt(): string
    {
        $constant = $this->wpConfigFile->getConstant('AUTH_SALT');
        if (!is_string($constant)) {
            throw new InstallationException(
                "Expected AUTH_SALT to be a string, got: " . gettype($constant),
                InstallationException::CONST_NOT_STRING
            );
        }
        return $constant;
    }

    /**
     * @throws InstallationException
     */
    public function getSecureAuthSalt(): string
    {
        $constant = $this->wpConfigFile->getConstant('SECURE_AUTH_SALT');
        if (!is_string($constant)) {
            throw new InstallationException(
                "Expected SECURE_AUTH_SALT to be a string, got: " . gettype($constant),
                InstallationException::CONST_NOT_STRING
            );
        }
        return $constant;
    }

    /**
     * @throws InstallationException
     */
    public function getLoggedInSalt(): string
    {
        $constant = $this->wpConfigFile->getConstant('LOGGED_IN_SALT');
        if (!is_string($constant)) {
            throw new InstallationException(
                "Expected LOGGED_IN_SALT to be a string, got: " . gettype($constant),
                InstallationException::CONST_NOT_STRING
            );
        }
        return $constant;
    }

    /**
     * @throws InstallationException
     */
    public function getNonceSalt(): string
    {
        $constant = $this->wpConfigFile->getConstant('NONCE_SALT');
        if (!is_string($constant)) {
            throw new InstallationException(
                "Expected NONCE_SALT to be a string, got: " . gettype($constant),
                InstallationException::CONST_NOT_STRING
            );
        }

        return $constant;
    }

    /**
     * @throws InstallationException
     */
    public function getTablePrefix(): string
    {
        $tablePrefix = $this->wpConfigFile->getVar('table_prefix');

        if (!is_string($tablePrefix)) {
            throw new InstallationException(
                "The table prefix is not a string.",
                InstallationException::TABLE_PREFIX_NOT_STRING
            );
        }

        return $tablePrefix;
    }


    public function getWpRootDir(string $path = ''): string
    {
        return $path ? $this->wpRootDir . ltrim($path, '\\/') : $this->wpRootDir;
    }

    public function getWpConfigPath(): string
    {
        return $this->wpConfigFile->getFilePath();
    }

    /**
     * @throws InstallationException|ProcessException|DbException|WpConfigFileException|Throwable
     */
    private function buildConfigured(string $wpRootDir, string $wpConfigFilePath): void
    {
        $this->wpRootDir = $this->checkWPRootDir($wpRootDir);

        if (!is_file($wpRootDir . '/wp-load.php')) {
            throw new InstallationException(
                'The WordPress installation is not configured.',
                InstallationException::STATE_EMPTY
            );
        }

        if (!is_file($wpConfigFilePath)) {
            throw new InstallationException(
                "Installation wp-config.php $wpConfigFilePath file not found.",
                InstallationException::WP_CONFIG_FILE_NOT_FOUND
            );
        }

        $this->wpConfigFile = new WPConfigFile($this->wpRootDir, $wpConfigFilePath);
        if ($this->wpConfigFile->usesMySQL()) {
            $this->db = MysqlDatabase::fromWpConfigFile($this->wpConfigFile);
        } else {
            $this->db = SQLiteDatabase::fromWpConfigFile($this->wpConfigFile);
        }
    }

    public function getDb(): DatabaseInterface
    {
        return $this->db;
    }

    public function isConfigured(): bool
    {
        return true;
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
        return [
            'AUTH_KEY' => $this->getAuthKey(),
            'SECURE_AUTH_KEY' => $this->getSecureAuthKey(),
            'LOGGED_IN_KEY' => $this->getLoggedInKey(),
            'NONCE_KEY' => $this->getNonceKey(),
            'AUTH_SALT' => $this->getAuthSalt(),
            'SECURE_AUTH_SALT' => $this->getSecureAuthSalt(),
            'LOGGED_IN_SALT' => $this->getLoggedInSalt(),
            'NONCE_SALT' => $this->getNonceSalt(),
        ];
    }

    public function isMultisite(): bool
    {
        return $this->wpConfigFile->isDefinedConst('MULTISITE')
            && $this->wpConfigFile->getConstant('MULTISITE');
    }

    public function isSubdomainMultisite(): bool
    {
        return $this->isMultisite()
            && $this->wpConfigFile->getConstant('SUBDOMAIN_INSTALL');
    }

    /**
     * @return int|float|string|bool|array<int|string,mixed>|null
     */
    public function getConstant(string $constant): int|float|string|bool|null|array
    {
        return $this->wpConfigFile->getConstant($constant);
    }

    /**
     * @return array<string,int|float|string|bool|array<int|string,mixed>|null>
     */
    public function getConstants(): array
    {
        return $this->wpConfigFile->getConstants();
    }

    /**
     * @return array<string,mixed>
     */
    public function getGlobals(): array
    {
        return $this->wpConfigFile->getVariables();
    }

    /**
     * @throws InstallationException
     */
    public function getContentDir(string $path = ''): string
    {
        $contentDir = rtrim($this->wpRootDir, '\\/') . '/wp-content';

        if ($wpContentDirConst = $this->getConstant('WP_CONTENT_DIR')) {
            if (!is_string($wpContentDirConst)) {
                throw new InstallationException(
                    "The WP_CONTENT_DIR constant is not a string.",
                    InstallationException::CONST_NOT_STRING
                );
            }
            $contentDir = $wpContentDirConst;
        }

        $contentDir = rtrim($contentDir, '\\/');

        return $path ? $contentDir . '/' . ltrim($path, '\\/') : $contentDir;
    }

    /**
     * @throws InstallationException
     */
    public function getPluginsDir(string $path = ''): string
    {
        $pluginsDir = $this->getContentDir('plugins');

        if ($wpPluginDirConst = $this->getConstant('WP_PLUGIN_DIR')) {
            if (!is_string($wpPluginDirConst)) {
                throw new InstallationException(
                    "The WP_PLUGIN_DIR constant is not a string.",
                    InstallationException::CONST_NOT_STRING
                );
            }
            $pluginsDir = $wpPluginDirConst;
        }

        $pluginsDir = rtrim($pluginsDir, '\\/');

        return $path ? $pluginsDir . '/' . ltrim($path, '\\/') : $pluginsDir;
    }

    /**
     * @throws InstallationException
     */
    public function getThemesDir(string $path = ''): string
    {
        $themesDir = $this->getContentDir('themes');

        if ($wpContentDirConst = $this->getConstant('WP_CONTENT_DIR')) {
            if (!is_string($wpContentDirConst)) {
                throw new InstallationException(
                    "The WP_CONTENT_DIR constant is not a string.",
                    InstallationException::CONST_NOT_STRING
                );
            }
            $themesDir = $wpContentDirConst . '/themes';
        }

        $themesDir = rtrim($themesDir, '\\/');

        return $path ? $themesDir . '/' . ltrim($path, '\\/') : $themesDir;
    }

    /**
     * @throws InstallationException
     */
    public function getMuPluginsDir(string $path = ''): string
    {
        $muPluginsDir = $this->getContentDir('mu-plugins');

        if ($wpContentDirConst = $this->getConstant('WPMU_PLUGIN_DIR')) {
            if (!is_string($wpContentDirConst)) {
                throw new InstallationException(
                    "The WPMU_PLUGIN_DIR constant is not a string.",
                    InstallationException::CONST_NOT_STRING
                );
            }
            $muPluginsDir = $wpContentDirConst;
        }

        $muPluginsDir = rtrim($muPluginsDir, '\\/');

        return $path ? $muPluginsDir . '/' . ltrim($path, '\\/') : $muPluginsDir;
    }
}
