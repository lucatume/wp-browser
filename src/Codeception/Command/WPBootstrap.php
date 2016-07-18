<?php

namespace Codeception\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class WPBootstrap extends Bootstrap
{

    /**
     * Returns an array containing the names of the suites the command will scaffold.
     *
     * @return array
     */
    public static function getScaffoldedSuitesNames()
    {
        return ['acceptance', 'functional', 'integration', 'unit'];
    }

    public function getDescription()
    {
        return "Sets up a WordPress CodeCeption testing environment.";
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
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

    public function createGlobalConfig()
    {
        $basicConfig = [
            'actor' => $this->actorSuffix,
            'paths' => [
                'tests' => 'tests',
                'log' => $this->logDir,
                'data' => $this->dataDir,
                'helpers' => $this->supportDir
            ],
            'settings' => [
                'bootstrap' => '_bootstrap.php',
                'colors' => (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN'),
                'memory_limit' => '1024M'
            ],
        ];

        $str = Yaml::dump($basicConfig, 4);
        if ($this->namespace) {
            $str = "namespace: {$this->namespace}\n" . $str;
        }
        file_put_contents('codeception.yml', $str);
    }

    /**
     * @param OutputInterface $output
     */
    protected function setupSuites(OutputInterface $output)
    {
        $this->createUnitSuite();
        $output->writeln("tests/unit created                    <- unit tests");
        $output->writeln("tests/unit.suite.yml written          <- unit tests suite configuration");
        $this->createIntegrationSuite();
        $output->writeln("tests/integration created             <- integration tests");
        $output->writeln("tests/integration.suite.yml written   <- integration tests suite configuration");
        $this->createFunctionalSuite();
        $output->writeln("tests/functional created              <- functional tests");
        $output->writeln("tests/functional.suite.yml written    <- functional tests suite configuration");
        $this->createAcceptanceSuite();
        $output->writeln("tests/acceptance created              <- acceptance tests");
        $output->writeln("tests/acceptance.suite.yml written    <- acceptance tests suite configuration");
    }

    protected function createIntegrationSuite($actor = 'Integration')
    {
        $suiteConfig = $this->getIntegrationSuiteConfig($actor);

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# Suite for integration tests.\n";
        $str .= "# Load WordPress and test classes that rely on its functions and classes.\n";
        $str .= $suiteConfig;
        $this->createSuite('integration', $actor, $str);
    }

    /**
     * @param $actor
     *
     * @return array
     */
    protected function getIntegrationSuiteConfig($actor)
    {
        $className = $actor . $this->actorSuffix;

        $suiteConfig = <<< YAML
class_name: {$className}
modules:
    enabled:
        - \\{$this->namespace}Helper\\{$actor}
        - WPLoader:
            wpRootFolder: /var/www/wordpress
            dbName: wordpress-tests
            dbHost: localhost
            dbUser: root
            dbPassword: root
            tablePrefix: wp_
            domain: wp.local
            adminEmail: admin@wp.local
            title: WP Tests
            plugins: 
                - hello.php
                - my-plugin/my-plugin.php
            activatePlugins: 
                - hello.php
                - my-plugin/my-plugin.php
            bootstrapActions:
                - my-first-action
                - my-second-action
YAML;

        return $suiteConfig;
    }

    protected function createFunctionalSuite($actor = 'Functional')
    {
        $suiteConfig = $this->getFunctionalSuiteConfig($actor);

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# Suite for WordPress functional tests.\n";
        $str .= "# Emulate web requests and make the WordPress application process them.\n";
        $str .= $suiteConfig;
        $this->createSuite('functional', $actor, $str);
    }

    /**
     * @param $actor
     *
     * @return array
     */
    protected function getFunctionalSuiteConfig($actor)
    {
        $className = $actor . $this->actorSuffix;

        $suiteConfig = <<< YAML
class_name: $className
modules:
    enabled:
        - \\{$this->namespace}Helper\\{$actor}
        - Filesystem,
        - WPDb:
            dsn: mysql:host=localhost;dbname=wordpress-tests
            user: root
            password: root
            dump: tests/_data/dump.sql
            populate: true
            cleanup: true
            url: http://wp.local
            tablePrefix: wp_
        - WordPress:
            depends: WPDb
            wpRootFolder: /var/www/wordpress
            adminUsername: admin
            adminPassword: password
YAML;


        return $suiteConfig;
    }

    protected function createAcceptanceSuite($actor = 'Acceptance')
    {
        $suiteConfig = $this->getAcceptanceSuiteConfig($actor);

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# Suite for WordPress acceptance tests.\n";
        $str .= "# Perform tests using or simulating a browser.\n";

        $str .= $suiteConfig;
        $this->createSuite('acceptance', $actor, $str);
    }

    /**
     * @param $actor
     *
     * @return array
     */
    protected function getAcceptanceSuiteConfig($actor)
    {
        $className = $actor . $this->actorSuffix;

        $suiteConfig = <<< YAML
class_name: $className
modules:
    enabled:
        - \\{$this->namespace}Helper\\{$actor}
        - WPBrowser
            url: http://wp.local
            adminUsername: admin
            adminPassword: password
            adminPath: /wp-admin
YAML;


        return $suiteConfig;
    }
}
