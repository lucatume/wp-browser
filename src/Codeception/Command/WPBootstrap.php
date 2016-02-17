<?php

namespace Codeception\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class WPBootstrap extends Bootstrap
{

    public function getDescription()
    {
        return "Sets up a WordPress CodeCeption testing environment.";
    }

    public function createGlobalConfig()
    {
        $basicConfig = [
            'actor' => $this->actorSuffix,
            'paths' => [
                'tests'   => 'tests',
                'log'     => $this->logDir,
                'data'    => $this->dataDir,
                'helpers' => $this->supportDir
            ],
            'settings' => [
                'bootstrap' => '_bootstrap.php',
                'colors' => (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN'),
                'memory_limit' => '1024M'
            ],
            'modules' => [
                'config' => [
                    'Db' => [
                        'dsn' => 'mysql:host=localhost;dbname=wordpress-tests',
                        'user' => 'root',
                        'password' => 'root',
                        'dump' => 'tests/_data/dump.sql'
                    ],
                    'WPBrowser' => [
                        'url' => 'http://wp.local',
                        'adminUsername' => 'adminUsername',
                        'adminPassword' => 'adminPassword',
                        'adminUrl' => '/wp-admin'
                    ],
                    'WPDb' => [
                        'dsn' => 'mysql:host=localhost;dbname=wordpress-tests',
                        'user' => 'root',
                        'password' => 'root',
                        'dump' => 'tests/_data/dump.sql',
                        'populate' => true,
                        'cleanup' => true,
                        'url' => 'http://wp.local',
                        'tablePrefix' => 'wp_'
                    ],
                    'WPLoader' => [
                        'wpRootFolder' => '~/www/wordpress',
                        'dbName' => 'wordpress-tests',
                        'dbHost' => 'localhost',
                        'dbUser' => 'root',
                        'dbPassword' => 'root',
                        'wpDebug' => true,
                        'dbCharset' => 'utf8',
                        'dbCollate' => '',
                        'tablePrefix' => 'wp_',
                        'domain' => 'wp.local',
                        'adminEmail' => 'admin@wp.local',
                        'title' => 'WP Tests',
                        'phpBinary' => 'php',
                        'language' => '',
                        'plugins' => ['hello.php', 'my-plugin/my-plugin.php'],
                        'activatePlugins' => ['hello.php', 'my-plugin/my-plugin.php'],
                        'bootstrapActions' => ['my-first-action', 'my-second-action']
                    ],
                    'WPWebDriver' => [
                        'url' => 'http://wp.local',
                        'browser' => 'phantomjs',
                        'port' => 4444,
                        'restart' => true,
                        'wait' => 2,
                        'adminUsername' => 'adminUsername',
                        'adminPassword' => 'adminPassword',
                        'adminUrl' => '/wp-admin'
                    ]
                ]
            ]
        ];

        $str = Yaml::dump($basicConfig, 4);
        if ($this->namespace) {
            $str = "namespace: {$this->namespace}\n" . $str;
        }
        file_put_contents('codeception.yml', $str);
    }

    protected function createFunctionalSuite($actor = 'Functional')
    {
        $suiteConfig = $this->getFunctionalSuiteConfig($actor);

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for WordPress functional tests.\n";
        $str .= "# Emulate web requests and make application process them.\n";
        $str .= Yaml::dump($suiteConfig, 2);
        $this->createSuite('functional', $actor, $str);
    }

    public function execute( InputInterface $input, OutputInterface $output ) {
        if ($input->getOption('namespace')) {
            $this->namespace = trim($input->getOption('namespace'), '\\') . '\\';
        }

        if ($input->getOption('actor')) {
            $this->actorSuffix = $input->getOption('actor');
        }

        $path = $input->getArgument('path');

        if (!is_dir($path)) {
            $output->writeln("<error>\nDirectory '$path' does not exist\n</error>");
            return;
        }

        $realpath = realpath($path);
        chdir($path);

        if (file_exists('codeception.yml')) {
            $output->writeln("<error>\nProject is already initialized in '$path'\n</error>");
            return;
        }

        $output->writeln(
            "<fg=white;bg=magenta> Initializing Codeception in " . $realpath . " </fg=white;bg=magenta>\n"
        );

        $this->createGlobalConfig();
        $output->writeln("File codeception.yml created       <- global configuration");

        $this->createDirs();

        if (!$input->getOption('empty')) {
            $this->setupSuites($output);
        }

        if (file_exists('.gitignore')) {
            file_put_contents('tests/_output/.gitignore', '');
            file_put_contents('.gitignore', file_get_contents('.gitignore') . "\ntests/_output/*");
            $output->writeln("tests/_output was added to .gitignore");
        }

        $output->writeln(" --- ");
        $this->ignoreFolderContent('tests/_output');

        file_put_contents('tests/_bootstrap.php', "<?php\n// This is global bootstrap for autoloading\n");
        $output->writeln("tests/_bootstrap.php written <- global bootstrap file");

        $output->writeln("<info>Building initial {$this->actorSuffix} classes</info>");
        $this->getApplication()->find('build')->run(
            new ArrayInput(['command' => 'build']),
            $output
        );

        $output->writeln("<info>\nBootstrap is done. Check out " . $realpath . "/tests directory</info>");
    }

    protected function createWpunitSuite($actor = 'Wpunit')
    {
        $suiteConfig = $this->getWpunitSuiteConfig($actor);

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for WordPress unit tests.\n";
        $str .= "# Load WordPress and unit test classes that rely on it.\n";
        $str .= Yaml::dump($suiteConfig, 2);
        $this->createSuite('wpunit', $actor, $str);
    }

    protected function createAcceptanceSuite($actor = 'Acceptance')
    {
        $suiteConfig = $this->getAcceptanceSuiteConfig($actor);

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for WordPress acceptance tests.\n";
        $str .= "# perform tests in browser using WPBrowser or WPWebDriver modules.\n";

        $str .= Yaml::dump($suiteConfig, 5);
        $this->createSuite('acceptance', $actor, $str);
    }

    /**
     * @param $actor
     *
     * @return array
     */
    protected function getFunctionalSuiteConfig($actor)
    {
        $suiteConfig = [
            'class_name' => $actor . $this->actorSuffix,
            'modules' => [
                'enabled' => [
                    'Filesystem',
                    'WPLoader',
                    "\\{$this->namespace}Helper\\{$actor}"
                ]
            ]
        ];

        return $suiteConfig;
    }

    /**
     * @param $actor
     *
     * @return array
     */
    protected function getWpunitSuiteConfig($actor)
    {
        $suiteConfig = [
            'class_name' => $actor . $this->actorSuffix,
            'modules' => [
                'enabled' => [
                    'WPLoader',
                    "\\{$this->namespace}Helper\\{$actor}"
                ]
            ]
        ];

        return $suiteConfig;
    }

    /**
     * @param $actor
     *
     * @return array
     */
    protected function getAcceptanceSuiteConfig($actor)
    {
        $suiteConfig = array(
            'class_name' => $actor . $this->actorSuffix,
            'modules' => [
                'enabled' => [
                    'WPBrowser',
                    "\\{$this->namespace}Helper\\{$actor}"
                ],
            ]
        );

        return $suiteConfig;
    }

    /**
     * @param OutputInterface $output
     */
    protected function setupSuites(OutputInterface $output)
    {
        $this->createUnitSuite();
        $output->writeln("tests/unit created                 <- unit tests");
        $output->writeln("tests/unit.suite.yml written       <- unit tests suite configuration");
        $this->createWpunitSuite();
        $output->writeln("tests/wpunit created                 <- WordPress unit tests");
        $output->writeln("tests/wpunit.suite.yml written       <- WordPress unit tests suite configuration");
        $this->createFunctionalSuite();
        $output->writeln("tests/functional created           <- functional tests");
        $output->writeln("tests/functional.suite.yml written <- functional tests suite configuration");
        $this->createAcceptanceSuite();
        $output->writeln("tests/acceptance created           <- acceptance tests");
        $output->writeln("tests/acceptance.suite.yml written <- acceptance tests suite configuration");
    }
}
