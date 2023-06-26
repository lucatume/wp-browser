<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use Closure;
use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\Utils\Strings;
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

class Configured implements InstallationStateInterface
{
    use WordPressChecks;
    use ConfiguredStateTrait;
    use ScaffoldedStateTrait;

    /**
     * @throws InstallationException|ProcessException|DbException|WpConfigFileException|Throwable
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
    private function validateInstallationParameters(
        string $url,
        string $adminUser,
        string $adminPassword,
        string $adminEmail,
        string $title
    ): void {
        /** @noinspection BypassedUrlValidationInspection */
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InstallationException(
                "The URL $url is not a valid URL.",
                InstallationException::INVALID_URL
            );
        }

        $safeAdminUser = Strings::slug($adminUser);
        if ($adminUser === '' || $safeAdminUser !== $adminUser) {
            throw new InstallationException(
                "The admin user $adminUser is not a valid user name.",
                InstallationException::INVALID_ADMIN_USERNAME
            );
        }

        if (empty($adminPassword)) {
            throw new InstallationException(
                "The admin password is empty.",
                InstallationException::INVALID_ADMIN_PASSWORD
            );
        }

        if (!filter_var($adminEmail, FILTER_SANITIZE_EMAIL)) {
            throw new InstallationException(
                "The admin email $adminEmail is not a valid email address.",
                InstallationException::INVALID_ADMIN_EMAIL
            );
        }

        if (empty($title)) {
            throw new InstallationException(
                "The site title is empty.",
                InstallationException::INVALID_TITLE
            );
        }
    }

    /**
     * @throws DbException
     * @throws InstallationException
     * @throws ProcessException
     * @throws Throwable
     * @throws WorkerException
     * @throws WpConfigFileException
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

            if (empty($domain)) {
                throw new InstallationException(
                    "The URL $url is not a valid URL.",
                    InstallationException::INVALID_URL
                );
            }

            $closuresFactory = new CodeExecutionFactory($this->wpRootDir, $domain);
            $toInstallWordPress = $closuresFactory->toInstallWordPress(
                $title,
                $adminUser,
                $adminPassword,
                $adminEmail,
                $url
            );
            $jobs = [$toInstallWordPress];

            if ($this->isMultisite()) {
                $jobs[] = $closuresFactory->toInstallWordPressNetwork(
                    $adminEmail,
                    $title,
                    $this->isSubdomainMultisite()
                );
            }

            foreach ((new Loop($jobs, 1, true))->run()->getResults() as $result) {
                if ($result->getExitCode() !== 0) {
                    $returnValue = $result->getReturnValue();

                    if ($returnValue instanceof Throwable) {
                        throw $returnValue;
                    }

                    $reason = $result->getStderrBuffer()
                        ?: $result->getStdoutBuffer()
                            ?: 'unknown reason, use debug mode to see more.';
                    throw new InstallationException($reason, InstallationException::INSTALLATION_FAIL);
                }
            }
        } catch (Throwable $e) {
            throw new InstallationException(
                'WordPress installation failed. ' . $e->getMessage(),
                InstallationException::INSTALLATION_FAIL,
                $e
            );
        }

        return $this->isMultisite() ?
            new Multisite($this->wpRootDir, $this->wpConfigFile->getFilePath()) :
            new Single($this->wpRootDir, $this->wpConfigFile->getFilePath());
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

    /**
     * @throws InstallationException
     */
    public function updateOption(string $option, mixed $value): int
    {
        throw new InstallationException(
            'The WordPress installation has not been installed yet.',
            InstallationException::STATE_CONFIGURED
        );
    }

    /**
     * @throws InstallationException
     */
    public function executeClosureInWordPress(Closure $closure): mixed
    {
        throw new InstallationException(
            'The WordPress installation has not been installed yet.',
            InstallationException::STATE_CONFIGURED
        );
    }
}
