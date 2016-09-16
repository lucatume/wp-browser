<?php

namespace Codeception\Command;

use Codeception\Lib\Generator\AcceptanceSuiteConfig;
use Codeception\Lib\Generator\FunctionalSuiteConfig;
use Codeception\Lib\Generator\IntegrationSuiteConfig;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use tad\WPBrowser\Interactions\WPBootsrapButler;

class WPBootstrap extends Bootstrap
{
    /**
     * @var array
     */
    public $userConfig = [];
    /**
     * @var WPBootsrapButler
     */
    private $butler;

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

        if ($input->getOption('interactive')) {
            $this->userConfig = $this->butler->askQuestions($this->getHelper('question'), $input, $output);
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

        if (!$input->getOption('no-build')) {
            $this->getApplication()->find('build')->run(
                new ArrayInput(['command' => 'build']),
                $output
            );
        }

        $output->writeln("<info>\nBootstrap is done. Check out " . $realpath . "/tests directory</info>");
    }

    public function __construct($name, WPBootsrapButler $butler = null)
    {
        parent::__construct($name);
        $this->butler = $butler ?: new WPBootsrapButler();
    }

    public function createGlobalConfig()
    {
        $basicConfig = [
            'actor' => $this->actorSuffix,
            'paths' => [
                'tests' => 'tests',
                'log' => $this->logDir,
                'data' => $this->dataDir,
                'helpers' => $this->supportDir,
            ],
            'settings' => [
                'bootstrap' => '_bootstrap.php',
                'colors' => (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN'),
                'memory_limit' => '1024M',
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
        $str .= "\n\n";
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
        $defaults = [
            'actor' => $actor,
            'className' => $className,
            'namespace' => $this->namespace,
        ];

        $wploaderDefaults = $this->getWploaderDefaults();

        $settings = array_merge($defaults, $wploaderDefaults, $this->userConfig);

        return (new IntegrationSuiteConfig($settings))->produce();
    }

    protected function createFunctionalSuite($actor = 'Functional')
    {
        $suiteConfig = $this->getFunctionalSuiteConfig($actor);

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# Suite for WordPress functional tests.\n";
        $str .= "# Emulate web requests and make the WordPress application process them.\n";
        $str .= "\n\n";
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
        $defaults = [
            'actor' => $actor,
            'className' => $className,
            'namespace' => $this->namespace,
        ];

        $wpdbDefaults = $this->getWpdbConfigDefaults();
        $wordpressDefaults = $this->getWordpressConfigDefaults();

        $settings = array_merge($defaults, $wpdbDefaults, $wordpressDefaults, $this->userConfig);

        return (new FunctionalSuiteConfig($settings))->produce();
    }

    protected function createAcceptanceSuite($actor = 'Acceptance')
    {
        $suiteConfig = $this->getAcceptanceSuiteConfig($actor);

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# Suite for WordPress acceptance tests.\n";
        $str .= "# Perform tests using or simulating a browser.\n";
        $str .= "\n\n";
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

        $defaults = [
            'actor' => $actor,
            'className' => $className,
            'namespace' => $this->namespace,
        ];

        $wpdbDefaults = $this->getWpdbConfigDefaults();
        $wpbrowserDefaults = $this->getWpbrowserDefaults();

        $settings = array_merge($defaults, $wpdbDefaults, $wpbrowserDefaults, $this->userConfig);

        return (new AcceptanceSuiteConfig($settings))->produce();
    }

    protected function configure()
    {
        parent::configure();
        $this->addOption('no-build', null, InputOption::VALUE_NONE, 'Don\'t build after the bootstrap');
        $this->addOption('interactive', 'i', InputOption::VALUE_NONE, 'Interactive bootstrap');
    }

    /**
     * @return array
     */
    protected function getWpdbConfigDefaults()
    {
        $wpdbDefaults = [
            'dbHost' => 'localhost',
            'dbName' => 'wordpress-tests',
            'dbUser' => 'root',
            'dbPassword' => '',
            'url' => 'http://wp.local',
            'tablePrefix' => 'wp_',
        ];
        return $wpdbDefaults;
    }

    /**
     * @return array
     */
    protected function getWordpressConfigDefaults()
    {
        $wordpressDefaults = [
            'wpRootFolder' => '/var/www/wordpress',
            'adminUsername' => 'admin',
            'adminPassword' => 'password',
        ];
        return $wordpressDefaults;
    }

    /**
     * @return array
     */
    protected function getWpbrowserDefaults()
    {
        $wpbrowserDefaults = [
            'url' => 'http://wp.local',
            'adminUsername' => 'admin',
            'adminPassword' => 'password',
            'adminPath' => '/wp-admin',
        ];
        return $wpbrowserDefaults;
    }

    protected function getWploaderDefaults()
    {
        $wploaderDefaults = [
            'wpRootFolder' => '/var/www/wordpress',
            'dbName' => 'wordpress-tests',
            'dbHost' => 'localhost',
            'dbUser' => 'root',
            'dbPassword' => '',
            'tablePrefix' => 'int_',
            'domain' => 'wp.local',
            'adminEmail' => 'admin@wp.local',
            'plugins' => Yaml::dump(['hello.php'], 0)
        ];
        return $wploaderDefaults;
    }
}
