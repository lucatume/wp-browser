<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Db;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequestClosureFactory;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequestFactory;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\Traits\WordPressChecks;
use lucatume\WPBrowser\WordPress\Version;

class Configured implements InstallationStateInterface
{
    use WordPressChecks;
    use ConfiguredStateTrait;
    use ScaffoldedStateTrait;

    /**
     * @throws InstallationException|ProcessException
     * @throws DbException
     */
    public function __construct(string $wpRootDir, string $wpConfigFilePath)
    {
        $this->buildConfigured($wpRootDir, $wpConfigFilePath);
        $this->version = new Version($this->wpRootDir);
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
            'The WordPress installation is already configured and installed.',
            InstallationException::STATE_CONFIGURED
        );
    }

    /**
     * @throws InstallationException|ProcessException|DbException
     */
    public function install(
        string $url,
        string $adminUser,
        string $adminPassword,
        string $adminEmail,
        string $title
    ): InstallationStateInterface {
        $this->validateInstallationParameters($url, $adminUser, $adminPassword, $adminEmail, $title);

        codecept_debug("Installing WordPress in $this->wpRootDir ...");

        try {
            $this->db->create();
            $domain = parse_url($url, PHP_URL_HOST);
            $requestClosuresFactory = new FileRequestClosureFactory(new FileRequestFactory($this->wpRootDir, $domain));
            $request = $requestClosuresFactory->toInstall(
                $title,
                $adminUser,
                $adminPassword,
                $adminEmail,
                $url,
            );
            $result = Loop::executeClosure($request);
            if ($result->getExitCode() !== 0) {
                $returnValue = $result->getReturnValue();

                if ($returnValue instanceof \Throwable) {
                    throw $returnValue;
                }

                $reason = $result->getStderrBuffer()
                    ?: $result->getStdoutBuffer()
                        ?: 'unknown reason, use debug mode to see more.';
                throw new InstallationException($reason, InstallationException::INSTALLATION_FAIL);
            }
        } catch (\Throwable $e) {
            throw new InstallationException(
                'Could not install WordPress: ' . $e->getMessage(),
                InstallationException::INSTALLATION_FAIL, $e
            );
        }

        return $this->isMultisite() ?
            new Multisite($this->wpRootDir, $this->wpConfigFile->getFilePath(), $this->db) :
            new Single($this->wpRootDir, $this->wpConfigFile->getFilePath(), $this->db);
    }

    /**
     * @throws InstallationException
     */
    public function convertToMultisite(bool $subdomainInstall = false): InstallationStateInterface
    {
        throw new InstallationException(
            'The WordPress installation has not been installed yet.',
            InstallationException::STATE_CONFIGURED
        );
    }

    /**
     * @throws InstallationException
     */
    public function scaffold(string $version = 'latest'): InstallationStateInterface
    {
        throw new InstallationException(
            'The WordPress installation has already been scaffolded and configured.',
            InstallationException::STATE_CONFIGURED
        );
    }
}
