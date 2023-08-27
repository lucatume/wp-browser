<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\WordPress\CodeExecution\CodeExecutionFactory;
use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Database\DatabaseInterface;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\Traits\WordPressChecks;
use lucatume\WPBrowser\WordPress\Version;
use lucatume\WPBrowser\WordPress\WpConfigFileException;
use Throwable;
use WP_User;

class Single implements InstallationStateInterface
{
    use WordPressChecks;
    use ConfiguredStateTrait;
    use ScaffoldedStateTrait;
    use InstalledTrait;
    use InstallationChecks;

    /**
     * @throws DbException
     * @throws InstallationException
     * @throws ProcessException
     * @throws WpConfigFileException
     * @throws Throwable
     * @throws WorkerException
     */
    public function __construct(string $wpRootDir, string $wpConfigFilePath, DatabaseInterface $db = null)
    {
        $this->buildConfigured($wpRootDir, $wpConfigFilePath);

        if ($this->wpConfigFile->getConstant('MULTISITE')) {
            throw new InstallationException(
                "The installation is a multi-site one.",
                InstallationException::STATE_MULTISITE
            );
        }

        if ($db !== null) {
            $this->db = $db;
            $this->db->setEnvVars();
        }

        if (!$this->isInstalled(false)) {
            throw new InstallationException(
                "The WordPress single installation is not installed.",
                InstallationException::STATE_CONFIGURED
            );
        }

        $this->version = new Version($this->wpRootDir);
        $this->codeExecutionFactory = new CodeExecutionFactory($wpRootDir, $this->getBlogDomain());
    }

    public function isMultisite(): bool
    {
        return false;
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
            'The WordPress installation is already configured and installed.',
            InstallationException::STATE_CONFIGURED
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
            'The WordPress installation is already configured and installed.',
            InstallationException::STATE_SINGLE
        );
    }

    /**
     * @throws DbException
     * @throws InstallationException
     * @throws ProcessException
     * @throws Throwable
     * @throws WorkerException
     * @throws WpConfigFileException
     */
    public function convertToMultisite(bool $subdomainInstall = false): InstallationStateInterface
    {
        $wpConfigFilePath = $this->wpConfigFile->getFilePath();
        $wpConfigFileContents = file_get_contents($wpConfigFilePath);

        /** @var string $adminEmail */
        $adminEmail = $this->executeClosureInWordPress(static function () {
            $admins = get_users(['role' => 'administrator', 'limit' => 1]);

            if (count($admins) === 0 || !$admins[0] instanceof WP_User) {
                throw new InstallationException(
                    "Could not find an administrator user.",
                    InstallationException::NO_ADMIN_USER_FOUND
                );
            }

            return $admins[0]->user_email;
        });

        if ($wpConfigFileContents === false) {
            throw new InstallationException(
                "Could not read $wpConfigFilePath file.",
                InstallationException::WP_CONFIG_FILE_NOT_FOUND
            );
        }

        $subdomainInstallString = $subdomainInstall ? 'true' : 'false';
        $multisiteConstantsBlock = <<< PHP

define( 'WP_ALLOW_MULTISITE', true );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', $subdomainInstallString );
define( 'DOMAIN_CURRENT_SITE', \$_SERVER['HTTP_HOST'] );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );

PHP;

        $placeholder = '/* That\'s all, stop editing! Happy publishing. */';

        if (!str_contains($wpConfigFileContents, $placeholder)) {
            throw new InstallationException(
                "Could not find the placeholder string in $wpConfigFilePath",
                InstallationException::WP_CONFIG_FILE_MISSING_PLACEHOLDER
            );
        }

        $wpConfigFileContents = str_replace(
            $placeholder,
            $multisiteConstantsBlock . PHP_EOL . $placeholder,
            $wpConfigFileContents
        );

        if (!file_put_contents($wpConfigFilePath, $wpConfigFileContents, LOCK_EX)) {
            throw new InstallationException(
                "Could not write to $wpConfigFilePath",
                InstallationException::WRITE_ERROR
            );
        }

        Loop::executeClosureOrFail(
            $this->codeExecutionFactory->toInstallWordPressNetwork(
                $adminEmail,
                $this->getBlogName(),
                $subdomainInstall
            )
        );

        return new Multisite($this->wpRootDir, $wpConfigFilePath);
    }

    /**
     * @throws InstallationException
     */
    public function scaffold(string $version = 'latest'): InstallationStateInterface
    {
        throw new InstallationException(
            'The WordPress installation is already scaffolded, configured and installed.',
            InstallationException::STATE_SINGLE
        );
    }

    /**
     * @throws Throwable
     */
    public function setDb(DatabaseInterface $db): InstallationStateInterface
    {
        $clone = clone $this;
        $clone->db = $db;
        $db->setEnvVars();
        $db->create();

        if (!$clone->isInstalled(false)) {
            return (new Configured($this->wpRootDir, $this->wpConfigFile->getFilePath()))->setDb($db);
        }


        return $clone;
    }
}
