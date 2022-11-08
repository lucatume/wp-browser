<?php

namespace lucatume\WPBrowser\Template;


use Codeception\Extension\RunFailed;
use Codeception\Template\Bootstrap;
use lucatume\WPBrowser\Command\GenerateWPAjax;
use lucatume\WPBrowser\Command\GenerateWPCanonical;
use lucatume\WPBrowser\Command\GenerateWPRestApi;
use lucatume\WPBrowser\Command\GenerateWPRestController;
use lucatume\WPBrowser\Command\GenerateWPRestPostTypeController;
use lucatume\WPBrowser\Command\GenerateWPUnit;
use lucatume\WPBrowser\Command\GenerateWPXMLRPC;
use lucatume\WPBrowser\Module\WPDb;
use lucatume\WPBrowser\Module\WPFilesystem;
use lucatume\WPBrowser\Module\WPLoader;
use lucatume\WPBrowser\Module\WPWebDriver;
use lucatume\WPBrowser\OCI\ComposeStack;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\System;
use lucatume\WPBrowser\Utils\WP;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;
use Symfony\Component\Yaml\Yaml;

class Wpbrowser extends Bootstrap
{
    private ?Installation $installation = null;
    private array $questions = [
        'url' => ['What is the URL of the WordPress site to test?', 'http://localhost'],
        'adminUsername' => ['What is the administrator username?', 'admin'],
        'adminPassword' => ['What is the administrator password?', 'password'],
    ];
    private array $cachedAnswers = [];
    private ?string $projectType;

    /**
     * @throws InstallationException
     */
    public function setup(): void
    {
        $this->say('This script will initialize wp-browser and Codeception for your current project.');
        $workDir = rtrim(codecept_root_dir(), '\\/');

        $projectType = $this->getProjectType($workDir);

        if (!$this->ask("Setting up for a WordPress $projectType, correct?", true)) {
            $projectType = $this->ask('What type of project is this?', ['site', 'plugin', 'theme']);
        }

        $rootDir = match ($projectType) {
            'site' => $workDir,
            'plugin', 'theme' => WP::findRootDirFromDir($workDir),
            default => throw new \InvalidArgumentException(message: "Unknown project type $projectType"),
        };

        if ($rootDir === false) {
            // Might happen for themes and plugins.
        }

        $this->installation = Installation::fromRootDir($rootDir);
        $this->projectType = $projectType;

        $this->createConfiguration($this->workDir);
    }

    private function getProjectType(string $rootDir): string
    {
        if (file_exists($rootDir . '/wp-config.php')) {
            // Root of a default structure WordPres installation.
            return 'site';
        }

        if (file_exists(dirname($rootDir) . '/wp-config.php') && !file_exists(dirname($rootDir) . '/wp-settings.php')) {
            // Root of a WordPress installation, but the wp-config.php file is in the parent directory.
            return 'site';
        }
    }

    private function createConfiguration(string $workDir): void
    {

        try {
            $this->checkInstalled($workDir);
            $this->say("Initializing Codeception and wp-browser in {$workDir} ...");
            $this->createDirectoryFor($workDir);
            $this->workDir = $workDir;
            chdir($workDir);
            $input = $this->input;

            if ($input->getOption('namespace')) {
                $this->namespace = trim($input->getOption('namespace'), '\\');
            }

            if ($input->hasOption('actor') && $input->getOption('actor')) {
                $this->actorSuffix = $input->getOption('actor');
            }

            $this->createDirs();

            if ($this->ask('Would you like to use ready-to-run container-based setup?', true)) {
                $this->setupContainerStack($this->installation, $this->projectType, 'tests/compose.yml');
            } else {
                throw new \RuntimeException('Non-container based setup not yet supported');
            }

            $this->createGlobalConfig();
            $this->createEnvFile();

            if ($input->hasOption('empty') && $input->getOption('empty')) {
                return;
            }

//            $this->createEnd2EndSuite();
        } catch (\Exception $e) {
            $this->cleanup($workDir);
        }
//        $this->createWpUnitSuite();
//
//        $this->say(" --- ");
//        $this->say();
//        $this->saySuccess('Codeception is installed for acceptance, functional, and unit testing');
//        $this->say();
//
//        $this->say("<bold>Next steps:</bold>");
//        $this->say('1. Edit <bold>tests/acceptance.suite.yml</bold> to set url of your application. Change PhpBrowser to WebDriver to enable browser testing');
//        $this->say("2. Edit <bold>tests/functional.suite.yml</bold> to enable a framework module. Remove this file if you don't use a framework");
//        $this->say("3. Create your first acceptance tests using <comment>codecept g:cest acceptance First</comment>");
//        $this->say("4. Write first test in <bold>tests/acceptance/FirstCest.php</bold>");
//        $this->say("5. Run tests using: <comment>codecept run</comment>");
    }

    public function createGlobalConfig(): void
    {
        $basicConfig = [
            'support_namespace' => $this->supportNamespace,
            'paths' => [
                'tests' => 'tests',
                'output' => $this->outputDir,
                'data' => $this->dataDir,
                'support' => $this->supportDir,
                'envs' => $this->envsDir,
            ],
            'params' => 'tests/.env',
            'actor_suffix' => 'Tester',
            'extensions' => [
                'enabled' => [RunFailed::class],
                'commands' => [
                    GenerateWPUnit::class,
                    GenerateWPRestApi::class,
                    GenerateWPRestController::class,
                    GenerateWPRestPostTypeController::class,
                    GenerateWPAjax::class,
                    GenerateWPCanonical::class,
                    GenerateWPXMLRPC::class,
                ]
            ],
            'modules' => [
                'config' => [
                    WPBrowser::class => [],
                    WPWebDriver::class => $this->getWpWebDriverConfig(),
                    WPDb::class => [],
                    WPFilesystem::class => [],
                    WPLoader::class => [],
                ]
            ]
        ];

        $str = Yaml::dump($basicConfig, 4);
        if ($this->namespace !== '') {
            $namespace = rtrim($this->namespace, '\\');
            $str = "namespace: {$namespace}\n" . $str;
        }
        $this->createFile('codeception.yml', $str);
    }

    private function createEnd2EndSuite(string $actor = 'End2End'): void
    {
        $suiteConfigPrefix = <<< EOF
# End-to-end test suite configuration.
#
# Drive a real browser using the WPWebDriver module to simulate a user interaction with your WordPress project.
# Hydrate and manipulate the database state using the WPDb module.
# Interact with WordPress installation files using the WPFilesystem module.

EOF;

        $end2EndSuiteConfig = [
            'actor' => $actor . $this->actorSuffix,
            'modules' => [
                'enabled' => [
                    WPWebDriver::class,
                    WPDb::class,
                    WPFilesystem::class,
                    'Asserts'
                ],
            ],
            'step_decorators' => '~'
        ];
        $this->createSuite(
            'End2End',
            $actor,
            $suiteConfigPrefix . PHP_EOL . PHP_EOL . Yaml::dump($end2EndSuiteConfig, 4)
        );
    }

    private function createIntegrationSuite(string $actor = 'Integration'): void
    {
        $suiteConfigPrefix = <<< EOF
# Integration ("WordPress unit") test suite configuration.
#
# Run integration tests on a clean WordPress installation.

EOF;

        $integrationSuiteConfig = [
            'actor' => $actor . $this->actorSuffix,
            'modules' => [
                'enabled' => [
                    WPloader::class,
                ],
            ],
            'step_decorators' => '~'
        ];
        $this->createSuite(
            'Integration',
            $actor,
            $suiteConfigPrefix . PHP_EOL . PHP_EOL . Yaml::dump($integrationSuiteConfig, 4)
        );
    }

    private function cleanup(string $workDir): void
    {
        foreach ([
                     $workDir . '/codeception.yml',
                     $workDir . '/tests',

                 ] as $file) {
            FS::rrmdir($file);
        }
    }

    private function getWpWebDriverConfig(): array
    {
        $config = [
            'url' => '%WORDPRESS_TEST_URL%',
            'adminUsername' => '%WORDPRESS_TEST_ADMIN_USERNAME%',
            'adminPassword' => '%WORDPRESS_TEST_ADMIN_PASSWORD%',
            'adminPath' => '%WORDPRESS_TEST_ADMIN_PATH%',
            'browser' => 'chrome',
            'host' => '%WORDPRESS_TEST_BROWSER_HOST%',
            'port' => '%WORDPRESS_TEST_BROWSER_PORT%',
            'window_size' => '%WORDPRESS_TEST_BROWSER_WINDOW_SIZE%',
            'capabilities' => [
                'chromeOptions' => [
                    'args' => [
                        '--headless',
                        '--disable-gpu'
                    ]
                ]
            ]
        ];

        return $config;
    }

    private function getOrAsk(string $key): mixed
    {

        if (!isset($this->cachedAnswers[$key])) {
            [$questionText, $defaultValue] = $this->questions[$key] ?? [$key, null];
            $askQuestion = function () use ($questionText, $defaultValue) {
                return $this->ask($questionText, $defaultValue);
            };
            $this->cachedAnswers[$key] = $askQuestion();
        }

        return $this->cachedAnswers[$key];
    }

    private function setupContainerStack(
        Installation $installation,
        string $projectType,
        string $stackFileRelativePath
    ): void {
//        $OCIRuntime = Containers::OCIRuntime();
//        $composeRuntime = Containers::composeRuntime();
        $rootDir = $installation->getRootDir();
        $stackFile = $rootDir . '/' . $stackFileRelativePath;
        $stack = new ComposeStack([
            'services' => [
                'db' => [
                    'image' => 'mysql:latest',
                    'environment' => [
                        'MYSQL_DATABASE' => 'test',
                        'MYSQL_ALLOW_EMPTY_PASSWORD' => true,
                        'MYSQL_ROOT_PASSWORD' => '',
                        'MYSQL_USER' => 'test',
                        'MYSQL_PASSWORD' => 'test',
                    ],
                    'ports' => [3306],
                ],
                'wordpress' => [
                    'image' => 'wordpress:latest',
                    'ports' => [80],
                    'depends_on' => [
                       'db' => [
                           'condition' => 'service_healthy'
                       ]
                    ],
                ],
                'chrome' => [
                    'image' => (System::isArm() ? 'seleniarm/standalone-chromium:latest' : 'selenium/standalone-chrome:latest'),
                    'ports' => [4444],
                    'depends_on' => [
                        'wordpress' => [
                            'condition' => 'service_healthy'
                        ]
                    ],
                ],
            ],
        ], $this->workDir);

        if (!file_put_contents($stackFile, $stack->dump())) {
            throw new \RuntimeException("Could not write stack file $stackFile");
        }

        $stack->up()->waitForHealthy();
        $wordpressLocalhostPort = $stack->getLocalhostPort('wordpress', 80);
        $chromeLocalhostPort = $stack->getLocalhostPort('chrome', 4444);
        $dbLocalhostPort = $stack->getLocalhostPort('db', 3306);
    }

    private function createEnvFile(): void
    {
        $lines = [
            'WORDPRESS_TEST_URL=http://127.0.0.1:2389',
            'WORDPRESS_TEST_ADMIN_USERNAME=admin',
            'WORDPRESS_TEST_ADMIN_PASSWORD=password',
            'WORDPRESS_TEST_ADMIN_PATH=/wp-admin',
            'WORDPRESS_TEST_BROWSER_HOST=127.0.0.1',
            'WORDPRESS_TEST_BROWSER_PORT=2390',
            'WORDPRESS_TEST_BROWSER_WINDOW_SIZE=2000x2000',
        ];
        $envFileContents = implode(PHP_EOL, $lines) . PHP_EOL;

        if (!file_put_contents($this->workDir . '/tests/.env', $envFileContents)) {
            throw new \RuntimeException('Could not write tests/.env file');
        }
    }
}
