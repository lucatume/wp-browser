<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\WPConfigFile;
use lucatume\WPBrowser\WordPress\WpConfigFileException;
use Throwable;

trait ConfiguredStateTrait
{
    private WPConfigFile $wpConfigFile;
    private string $wpRootDir;
    private Db $db;

    public function getAuthKey(): string
    {
        return (string)$this->wpConfigFile->getConstant('AUTH_KEY');
    }

    public function getSecureAuthKey(): string
    {
        return (string)$this->wpConfigFile->getConstant('SECURE_AUTH_KEY');
    }

    public function getLoggedInKey(): string
    {
        return (string)$this->wpConfigFile->getConstant('LOGGED_IN_KEY');
    }

    public function getNonceKey(): string
    {
        return (string)$this->wpConfigFile->getConstant('NONCE_KEY');
    }

    public function getAuthSalt(): string
    {
        return (string)$this->wpConfigFile->getConstant('AUTH_SALT');
    }

    public function getSecureAuthSalt(): string
    {
        return (string)$this->wpConfigFile->getConstant('SECURE_AUTH_SALT');
    }

    public function getLoggedInSalt(): string
    {
        return (string)$this->wpConfigFile->getConstant('LOGGED_IN_SALT');
    }

    public function getNonceSalt(): string
    {
        return (string)$this->wpConfigFile->getConstant('NONCE_SALT');
    }

    public function getTablePrefix(): string
    {
        return $this->wpConfigFile->getVar('table_prefix');
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
        $this->db = Db::fromWpConfigFile($this->wpConfigFile);
    }

    public function getDb(): Db
    {
        return $this->db;
    }

    public function isConfigured(): bool
    {
        return true;
    }


    /**
     * @return array{AUTH_KEY: mixed, SECURE_AUTH_KEY: mixed, LOGGED_IN_KEY: mixed, NONCE_KEY: mixed, AUTH_SALT: mixed,
     *                         SECURE_AUTH_SALT: mixed, LOGGED_IN_SALT: mixed, NONCE_SALT: mixed}
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

    public function getConstant(string $constant): mixed
    {
        return $this->wpConfigFile->getConstant($constant);
    }

    /**
     * @return array<string,mixed>
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

    public function getContentDir(string $path = ''): string
    {
        $contentDir = rtrim($this->wpRootDir, '\\/') . '/wp-content';

        if ($wpContentDirConst = $this->getConstant('WP_CONTENT_DIR')) {
            $contentDir = $wpContentDirConst;
        }

        $contentDir = rtrim($contentDir, '\\/');

        return $path ? $contentDir . '/' . ltrim($path, '\\/') : $contentDir;
    }

    public function getPluginsDir(string $path = ''): string
    {
        $pluginsDir = $this->getContentDir('plugins');

        if ($wpPluginDirConst = $this->getConstant('WP_PLUGIN_DIR')) {
            $pluginsDir = $wpPluginDirConst;
        }

        $pluginsDir = rtrim($pluginsDir, '\\/');

        return $path ? $pluginsDir . '/' . ltrim($path, '\\/') : $pluginsDir;
    }

    public function getThemesDir(string $path = ''): string
    {
        $themesDir = $this->getContentDir('themes');

        if ($wpContentDirConst = $this->getConstant('WP_CONTENT_DIR')) {
            $themesDir = $wpContentDirConst . '/themes';
        }

        $themesDir = rtrim($themesDir, '\\/');

        return $path ? $themesDir . '/' . ltrim($path, '\\/') : $themesDir;
    }
}
