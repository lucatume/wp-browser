<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\Traits\WordPressChecks;
use lucatume\WPBrowser\WordPress\Version;

class Multisite implements InstallationStateInterface
{
    use WordPressChecks;
    use ScaffoldedStateTrait;
    use ConfiguredStateTrait;

    /**
     * @throws InstallationException|ProcessException|DbException
     */
    public function __construct(string $wpRootDir, string $wpConfigFilePath, Db $db)
    {
        $this->buildConfigured($wpRootDir, $wpConfigFilePath, $db);

        if (!is_file($wpRootDir . '/wp-load.php')) {
            throw new InstallationException(
                'The WordPress installation is not configured.',
                InstallationException::STATE_EMPTY
            );
        }
        if (!$this->wpConfigFile->getConstant('MULTISITE')) {
            throw new InstallationException(
                "The installation is not a multi-site one.",
                InstallationException::STATE_SINGLE
            );
        }
        $this->version = new Version($this->wpRootDir);
    }

    public function isMultisite(): bool
    {
        return true;
    }

    /**
     * @throws InstallationException
     */
    public function configure(
        Db $db,
        int $multisite = InstallationStateInterface::SINGLE_SITE,
        ?ConfigurationData $configurationData = null
    ): InstallationStateInterface {
        throw new InstallationException(
            'The WordPress installation is already configured.',
            InstallationException::STATE_MULTISITE
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
            'The WordPress installation is already configured.',
            InstallationException::STATE_MULTISITE
        );
    }

    /**
     * @throws InstallationException
     */
    public function convertToMultisite(bool $subdomainInstall = false): InstallationStateInterface
    {
        throw new InstallationException(
            'The WordPress installation is already configured as multisite.',
            InstallationException::STATE_MULTISITE
        );
    }

    /**
     * @throws InstallationException
     */
    public function scaffold(string $version = 'latest'): InstallationStateInterface
    {
        throw new InstallationException(
            'The WordPress installation is already scaffolded.',
            InstallationException::STATE_MULTISITE
        );
    }
}