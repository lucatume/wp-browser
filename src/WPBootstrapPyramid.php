<?php

namespace Codeception\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Creates default config, tests directory and sample suites for current project. Use this command to start building a test suite.
 * The suites created reflect the UI, Service and Unit test pyramid paradigm.
 *
 * By default it will create 3 suites: **ui**, **service**, and **unit**. To customize run this command with `--customize` option.
 *
 * For Codeception 1.x compatible setup run bootstrap in `--compat` option.
 *
 * * `codecept wpbootstrap` - creates `tests` dir and `codeception.yml` in current dir.
 * * `codecept wpbootstrap --customize` - set manually actors and suite names during setup
 * * `codecept wpbootstrap --compat` - prepare Codeception 1.x setup with Guy classes.
 * * `codecept wpbootstrap --namespace Frontend` - creates tests, and use `Frontend` namespace for actor classes and helpers.
 * * `codecept wpbootstrap --actor Wizard` - sets actor as Wizard, to have `TestWizard` actor in tests.
 * * `codecept wpbootstrap path/to/the/project` - provide different path to a project, where tests should be placed
 *
 */
class WPBootstrapPyramid extends Bootstrap
{
    public function getDescription()
    {
        return "Sets up a WordPress testing environment using the test pyramid suite organization.";
    }

    /**
     * Performs Codeception 1.x compatible setup using with Guy classes
     */
    protected function compatibilitySetup(OutputInterface $output)
    {
        $this->actorSuffix = 'Guy';

        $this->logDir = 'tests/_log';
        $this->helperDir = 'tests/_helpers';

        $this->createGlobalConfig();
        $output->writeln("File codeception.yml created       <- global configuration");

        $this->createDirs();

        $this->createUnitSuite('Code');
        $output->writeln("tests/unit created                 <- unit tests");
        $output->writeln("tests/unit.suite.yml written       <- unit tests suite configuration");
        $this->createServiceSuite('Service');
        $output->writeln("tests/service created           <- service tests");
        $output->writeln("tests/service.suite.yml written <- service tests suite configuration");
        $this->createUiSuite('UI');
        $output->writeln("tests/ui created           <- ui tests");
        $output->writeln("tests/ui.suite.yml written <- ui tests suite configuration");

        if (file_exists('.gitignore')) {
            file_put_contents('tests/_log/.gitignore', '');
            file_put_contents('.gitignore', file_get_contents('.gitignore') . "\ntests/_log/*");
            $output->writeln("tests/_log was added to .gitignore");
        }

    }

    protected function createUnitSuite($actor = 'Unit')
    {
        $suiteConfig = array(
            'class_name' => $actor . $this->actorSuffix,
            'modules' => array('enabled' => array('Asserts', $actor . 'Helper')),
        );

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for unit (internal) tests.\n";
        $str .= Yaml::dump($suiteConfig, 2);

        $this->createSuite('unit', $actor, $str);
    }

    protected function createServiceSuite($actor = 'Service')
    {
        $suiteConfig = array(
            'class_name' => $actor . $this->actorSuffix,
            'modules' => array(
                'enabled' => array(
                    'Filesystem',
                    'WPDb',
                    'WPLoader',
                    $actor . 'Helper'
                ),
                'config' => array(
                    'WPDb' => array(
                        'dsn' => 'mysql:host=localhost;dbname=testdb',
                        'user' => 'root',
                        'password' => '',
                        'dump' => 'tests/_data/dump.sql',
                        'populate' => true,
                        'cleanup' => true,
                        'url' => 'http://example.local',
                        'tablePrefix' => 'wp_',
                        'checkExistence' => true,
                        'update' => true
                    ),
                    'WPLoader' => array(
                        'wpRootFolder' => '/www/wordpress',
                        'dbName' => 'wpress-tests',
                        'dbHost' => 'localhost',
                        'dbUser' => 'root',
                        'dbPassword' => 'root',
                        'wpDebug' => true,
                        'dbCharset' => 'utf8',
                        'dbCollate' => '',
                        'tablePrefix' => 'wptests_',
                        'domain' => 'example.org',
                        'adminEmail' => 'admin@example.com',
                        'title' => 'Test Blog',
                        'phpBinary' => 'php',
                        'language' => ''
                    )
                )
            ),
        );

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for service (integration) tests.\n";
        $str .= "# Emulate web requests and make application process them.\n";
        $str .= Yaml::dump($suiteConfig, 2);
        $this->createSuite('service', $actor, $str);
    }

    protected function createUiSuite($actor = 'UI')
    {
        $suiteConfig = array(
            'class_name' => $actor . $this->actorSuffix,
            'modules' => array(
                'enabled' => array('WPBrowser', 'WPDb', $actor . 'Helper'),
                'config' => array(
                    'WPBrowser' => array(
                        'url' => 'http://example.local',
                        'adminUsername' => 'root',
                        'adminPassword' => 'root',
                        'adminUrl' => '/wp-core/wp-admin'
                    ),
                    'WPDb' => array(
                        'dsn' => 'mysql:host=localhost;dbname=testdb',
                        'user' => 'root',
                        'password' => '',
                        'dump' => 'tests/_data/dump.sql',
                        'populate' => true,
                        'cleanup' => true,
                        'url' => 'http://example.local',
                        'tablePrefix' => 'wp_',
                        'checkExistence' => true,
                        'update' => true
                    ),
                )
            ),
        );

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for acceptance tests.\n";
        $str .= "# perform tests in browser using WPBrowser.\n";

        $str .= Yaml::dump($suiteConfig, 5);
        $this->createSuite('ui', $actor, $str);
    }

    /**
     * @param OutputInterface $output
     */
    protected function setup(OutputInterface $output)
    {
        $this->createGlobalConfig();
        $output->writeln("File codeception.yml created       <- global configuration");

        $this->createDirs();
        $this->createUnitSuite();
        $output->writeln("tests/unit created                 <- unit tests");
        $output->writeln("tests/unit.suite.yml written       <- unit tests suite configuration");
        $this->createServiceSuite();
        $output->writeln("tests/service created           <- service tests");
        $output->writeln("tests/service.suite.yml written <- service tests suite configuration");
        $this->createUiSuite();
        $output->writeln("tests/ui created           <- ui tests");
        $output->writeln("tests/ui.suite.yml written <- ui tests suite configuration");

        if (file_exists('.gitignore')) {
            file_put_contents('tests/_output/.gitignore', '');
            file_put_contents('.gitignore', file_get_contents('.gitignore') . "\ntests/_output/*");
            $output->writeln("tests/_output was added to .gitignore");
        }
    }
}
