<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\WPConfigFile;

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


    public function getWpRootDir(): string
    {
        return $this->wpRootDir;
    }

    public function getWpConfigPath(): string
    {
        return $this->wpConfigFile->getFilePath();
    }

    /**
     * @throws InstallationException
     * @throws ProcessException
     * @throws DbException
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


    public function getSalts(): array
    {
        return [
            'authKey' => $this->getAuthKey(),
            'secureAuthKey' => $this->getSecureAuthKey(),
            'loggedInKey' => $this->getLoggedInKey(),
            'nonceKey' => $this->getNonceKey(),
            'authSalt' => $this->getAuthSalt(),
            'secureAuthSalt' => $this->getSecureAuthSalt(),
            'loggedInSalt' => $this->getLoggedInSalt(),
            'nonceSalt' => $this->getNonceSalt(),
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

    public function getConstants(): array
    {
        return $this->wpConfigFile->getConstants();
    }
}
