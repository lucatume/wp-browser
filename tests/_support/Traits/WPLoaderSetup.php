<?php

namespace lucatume\WPBrowser\Tests\Traits;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use lucatume\WPBrowser\Module\WPLoader;

/**
 * Shared setup/teardown and helpers for the WPLoader module unit tests.
 *
 * Extracted from the original monolithic WPLoaderTest.php so related tests
 * can live in smaller, more focused files while keeping identical lifecycle.
 */
trait WPLoaderSetup
{
    /**
     * @var array<string,mixed>
     */
    protected $config;

    /**
     * @var string|null
     */
    private $previousCwd;
    /**
     * @var string|null
     */
    private $homeEnvBackup;
    /**
     * @var string|null
     */
    private $homeServerBackup;
    /**
     * @var \Codeception\Lib\ModuleContainer|null
     */
    private $mockModuleContainer;

    /**
     * @after
     */
    public function restorePaths(): void
    {
        if ($this->previousCwd !== null) {
            chdir($this->previousCwd);
        }
        $this->previousCwd = null;

        if ($this->homeEnvBackup !== null) {
            putenv('HOME=' . $this->homeEnvBackup);
        }
        $this->homeEnvBackup = null;

        if ($this->homeServerBackup !== null) {
            $_SERVER['HOME'] = $this->homeServerBackup;
        }
        $this->homeServerBackup = null;
    }

    /**
     * @after
     */
    public function undefineConstants(): void
    {
        foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'] as $const) {
            if (defined($const)) {
                uopz_undefine($const);
            }
        }
    }

    /**
     * @after
     */
    public function unsetEnvVars(): void
    {
        foreach (['LOADED', 'LOADED_2', 'LOADED_3'] as $envVar) {
            putenv($envVar);
        }
    }

    /**
     * @param array<string,mixed> $moduleContainerConfig
     * @param array<string,mixed>|null $moduleConfig
     */
    protected function module(array $moduleContainerConfig = [], ?array $moduleConfig = null): WPLoader
    {
        $this->mockModuleContainer = new ModuleContainer(new Di(), $moduleContainerConfig);
        return new WPLoader($this->mockModuleContainer, ($moduleConfig ?? $this->config));
    }
}
