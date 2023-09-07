<?php

namespace lucatume\WPBrowser\Template;

use Codeception\Extension\RunFailed;
use Codeception\Template\Bootstrap;
use lucatume\WPBrowser\Command\DbExport;
use lucatume\WPBrowser\Command\DbImport;
use lucatume\WPBrowser\Command\GenerateWPUnit;
use lucatume\WPBrowser\Command\RunAll;
use lucatume\WPBrowser\Command\RunOriginal;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Project\PluginProject;
use lucatume\WPBrowser\Project\ProjectInterface;
use lucatume\WPBrowser\Project\SiteProject;
use lucatume\WPBrowser\Project\TestEnvironment;
use lucatume\WPBrowser\Project\ThemeProject;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class Wpbrowser extends Bootstrap
{
    private ?TestEnvironment $testEnvironment = null;

    /**
     * @throws RuntimeException
     */
    public function setup(): void
    {
        // At this stage Codeception changed the working directory to the work dir one.
        $workDir = getcwd();

        if ($workDir === false) {
            throw new RuntimeException('Could not get the current working directory.');
        }

        $project = $this->buildProjectFromWorkDir($workDir);

        $this->say('Initializing <info>wp-browser</info> for a <info>' . $project->getType() . '</info> project.');
        $this->say('You can quit this process at any time with <info>CTRL+C</info>.');

        $input = $this->input;
        $namespace = $input->hasOption('namespace') ? $input->getOption('namespace') : null;
        if ($namespace && is_string($namespace)) {
            $this->namespace = trim($namespace, '\\');
        }

        $actor = $input->hasOption('actor') ? $input->getOption('actor') : null;
        if ($actor && is_string($actor)) {
            $this->actorSuffix = $actor;
        }

        $this->createDirs();
        $this->sayInfo('Created <info>tests</info> directory and sub-directories.');

        try {
            $project->setup();
            $this->testEnvironment = $project->getTestEnv();
            $this->createGlobalConfig();
            $this->say('Created Codeception configuration file <info>codeception.yml</info>.');
            $this->createEnvFile();
            $this->createIntegrationSuite($project);
            $this->createEndToEndSuite($project);
        } catch (Throwable $e) {
            $this->sayError($e->getMessage());
            $this->sayError('Setup failed, check the error message above and try again.');
            $this->cleanUpOnFail();
            return;
        }

        /** @var TestEnvironment $testEnvironment */
        $testEnvironment = $this->testEnvironment;

        $this->say(PHP_EOL . 'Setup completed.' . PHP_EOL);
        if ($testEnvironment->afterSuccess !== null) {
            $testEnvironment->runAfterSuccess();
            $this->say();
        }
        $this->say('You can run tests with <info>vendor/bin/codecept run</info>.');
    }

    public function createGlobalConfig(): void
    {
        /** @var TestEnvironment $testEnv */
        $testEnv = $this->testEnvironment;
        $basicConfig = [
            'support_namespace' => $this->supportNamespace,
            'paths' => [
                'tests' => 'tests',
                'output' => $this->outputDir,
                'data' => $this->dataDir,
                'support' => $this->supportDir,
                'envs' => $this->envsDir,
            ],
            'actor_suffix' => 'Tester',
            'params' => ['tests/.env'],
            'extensions' => [
                'enabled' => [RunFailed::class, ...array_keys($testEnv->extensionsEnabled)],
                'config' => $testEnv->extensionsEnabled,
                'commands' => [
                    RunOriginal::class,
                    RunAll::class,
                    GenerateWPUnit::class,
                    DbExport::class,
                    DbImport::class,
                    ...$testEnv->customCommands
                ]
            ]
        ];

        $str = Yaml::dump($basicConfig, 6);
        if ($this->namespace !== '') {
            $namespace = rtrim($this->namespace, '\\');
            $str = "namespace: $namespace\n" . $str;
        }
        $this->createFile('codeception.yml', $str);
    }

    private function createIntegrationSuite(ProjectInterface $project): void
    {
        $plugins = '';
        if ($project instanceof PluginProject) {
            $plugins = "'{$project->getActivationString()}'";
        }
        $theme = '';
        if ($project instanceof ThemeProject) {
            $theme = $project->getActivationString();
        }

        $suiteConfig = <<<EOF
# Integration suite configuration
#
# Run integration and "WordPress unit" tests.

actor: Integration{$this->actorSuffix}
bootstrap: _bootstrap.php
modules:
    enabled:
        - lucatume\WPBrowser\Module\WPLoader
    config:
        lucatume\WPBrowser\Module\WPLoader:
           wpRootFolder: "%WORDPRESS_ROOT_DIR%" 
           dbUrl: '%WORDPRESS_DB_URL%'
           wpDebug: true
           tablePrefix: '%TEST_TABLE_PREFIX%'
           domain: '%WORDPRESS_DOMAIN%'
           adminEmail: 'admin@%WORDPRESS_DOMAIN%'
           title: 'Integration Tests'
           plugins: [$plugins]
           theme: '$theme'
EOF;
        $this->createSuite('Integration', 'Integration', $suiteConfig);
        $bootstrapContents = <<<EOF
<?php
/*
 * Integration suite bootstrap file.
 * 
 * This file is loaded AFTER the suite modules are initialized, WordPress, plugins and themes are loaded.
 * 
 * If you need to load plugins or themes, add them to the Integration suite configuration file, in the 
 * "modules.config.WPLoader.plugins" and "modules.config.WPLoader.theme" settings.
 * 
 * If you need to load one or more database dump file(s) to set up the test database, add the path to the dump file to
 * the "modules.config.WPLoader.dump" setting.
 */
EOF;
        $bootstrapPathname = $this->workDir . '/tests/Integration/_bootstrap.php';
        if (!file_put_contents($bootstrapPathname, $bootstrapContents, LOCK_EX)) {
            throw new RuntimeException(
                'Could not write to file ' . $this->workDir . '/tests/Integration/_bootstrap.php'
            );
        }

        $this->say('Created <info>Integration</info> suite and configuration.');
    }

    private function createEnvFile(): void
    {
        /** @var TestEnvironment $testEnv */
        $testEnv = $this->testEnvironment;

        $envFileContents = <<< ENV
# The path to the WordPress root directory, the one containing the wp-load.php file.
# This can be a relative path from the directory that contains the codeception.yml file,
# or an absolute path.
WORDPRESS_ROOT_DIR={$testEnv->wpRootDir}

# Tests will require a MySQL database to run.
# The database will be created if it does not exist.
# Do not use a database that contains important data!
WORDPRESS_DB_URL={$testEnv->dbUrl}

# The Integration suite will use this table prefix for the WordPress tables.
TEST_TABLE_PREFIX={$testEnv->testTablePrefix}

# This table prefix used by the WordPress site in end-to-end tests.
WORDPRESS_TABLE_PREFIX={$testEnv->wpTablePrefix}

# The URL and domain of the WordPress site used in end-to-end tests.
WORDPRESS_URL={$testEnv->wpUrl}
WORDPRESS_DOMAIN={$testEnv->wpDomain}

# The username and password of the administrator user of the WordPress site used in end-to-end tests.
WORDPRESS_ADMIN_USER={$testEnv->wpAdminUser}
WORDPRESS_ADMIN_PASSWORD={$testEnv->wpAdminPassword}

# The host and port of the ChromeDriver server that will be used in end-to-end tests.
CHROMEDRIVER_HOST={$testEnv->chromeDriverHost}
CHROMEDRIVER_PORT={$testEnv->chromeDriverPort}
ENV;

        if ($testEnv->extraEnvFileContents) {
            $envFileContents .= PHP_EOL . PHP_EOL . $testEnv->extraEnvFileContents;
        }

        file_put_contents('tests/.env', $envFileContents);
        $this->say('Created <info>tests/.env</info> file.');
        putenv("WORDPRESS_DB_URL=$testEnv->dbUrl");
        $_ENV['WORDPRESS_DB_URL'] = $testEnv->dbUrl;
    }

    private function createEndToEndSuite(ProjectInterface $project): void
    {
        $suiteConfig = <<<EOF
# Integration suite configuration
#
# Run integration and "WordPress unit" tests.

actor: EndToEnd{$this->actorSuffix}
bootstrap: _bootstrap.php
modules:
    enabled:
        - lucatume\WPBrowser\Module\WPWebDriver
        - lucatume\WPBrowser\Module\WPDb
        - lucatume\WPBrowser\Module\WPFilesystem
        - lucatume\WPBrowser\Module\WPLoader
    config:
        lucatume\WPBrowser\Module\WPWebDriver:
            url: '%WORDPRESS_URL%'
            adminUsername: '%WORDPRESS_ADMIN_USER%'
            adminPassword: '%WORDPRESS_ADMIN_PASSWORD%'
            adminPath: '/wp-admin'
            browser: chrome
            host: '%CHROMEDRIVER_HOST%'
            port: '%CHROMEDRIVER_PORT%'
            window_size: 1200x1000
            capabilities:
              "goog:chromeOptions":
                args:
                  - "--headless"
                  - "--disable-gpu"
                  - "--disable-dev-shm-usage"
                  - "--proxy-server='direct://'"
                  - "--proxy-bypass-list=*"
                  - "--no-sandbox"
        lucatume\WPBrowser\Module\WPDb:
            dbUrl: '%WORDPRESS_DB_URL%'
            dump: 'tests/Support/Data/dump.sql'
            populate: true
            cleanup: true
            reconnect: false
            url: '%WORDPRESS_URL%'
            urlReplacement: false
            tablePrefix: '%WORDPRESS_TABLE_PREFIX%'
        lucatume\WPBrowser\Module\WPFilesystem:
            wpRootFolder: '%WORDPRESS_ROOT_DIR%'
        lucatume\WPBrowser\Module\WPLoader:
            loadOnly: true
            wpRootFolder: "%WORDPRESS_ROOT_DIR%" 
            dbUrl: '%WORDPRESS_DB_URL%'
            domain: '%WORDPRESS_DOMAIN%'
            
EOF;
        $this->createSuite('EndToEnd', 'EndToEnd', $suiteConfig);
        $bootstrapContents = <<<EOF
<?php
/*
 * EndToEnd suite bootstrap file.
 * 
 * This file is loaded AFTER the suite modules are initialized and WordPress has been loaded by the WPLoader module.
 * 
 * The initial state of the WordPress site is the one set up by the dump file(s) loaded by the WPDb module, look for the
 * "modules.config.WPDb.dump" setting in the suite configuration file. The database will be dropped after each test
 * and re-created from the dump file(s).
 * 
 * You can modify and create new dump files using the `vendor/bin/codecept wp:cli EndToEnd <wp-cli command>` command
 * to run WP-CLI commands on the WordPress site and database used by the EndToEnd suite.
 * E.g.:
 * `vendor/bin/codecept wp:cli EndToEnd db import tests/Support/Data/dump.sql` to load  dump file.
 * `vendor/bin/codecept wp:cli EndToEnd plugin activate woocommerce` to activate the WooCommerce plugin.
 * `vendor/bin/codecept wp:cli EndToEnd user create alice alice@example.com --role=administrator` to create a new user.
 * `vendor/bin/codecept wp:cli EndToEnd db export tests/Support/Data/dump.sql` to update the dump file.
 */
EOF;
        $bootstrapPathname = $this->workDir . '/tests/EndToEnd/_bootstrap.php';
        if (!file_put_contents($bootstrapPathname, $bootstrapContents, LOCK_EX)) {
            throw new RuntimeException('Could not write to file '
                . $this->workDir . '/tests/Integration/_bootstrap.php');
        }

        $this->say('Created <info>EndToEnd</info> suite and configuration.');
    }

    private function buildProjectFromWorkDir(string $workDir): ProjectInterface
    {
        // If we find a style.css file in the work directory we assume it's a theme.
        if (ThemeProject::parseDir($workDir)) {
            return new ThemeProject($this->input, $this->output, $workDir);
        }

        if (PluginProject::parseDir($workDir)) {
            return new PluginProject($this->input, $this->output, $workDir);
        }

        // Assume it's a site.
        return new SiteProject($this->input, $this->output, $workDir);
    }

    protected function cleanUpOnFail(): void
    {
        FS::rrmdir($this->workDir . '/tests');
        if (is_file($this->workDir . '/codeception.yml')) {
            @unlink($this->workDir . '/codeception.yml');
        }
    }
}
