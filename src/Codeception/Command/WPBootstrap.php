<?php

namespace Codeception\Command;

use Codeception\Lib\Generator\AcceptanceSuiteConfig;
use Codeception\Lib\Generator\FunctionalSuiteConfig;
use Codeception\Lib\Generator\IntegrationSuiteConfig;
use Codeception\Lib\Generator\IntegrationSuiteThemeConfig;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use tad\WPBrowser\Console\Output\WrappingOutput;
use tad\WPBrowser\Interactions\ButlerInterface;
use tad\WPBrowser\Interactions\WPBootsrapButler;

class WPBootstrap extends Bootstrap {
	/**
	 * @var array
	 */
	public $userConfig = [];

	/**
	 * @var WPBootsrapButler
	 */
	protected $butler;

	/**
	 * @var string|array Either a theme slug or an array in the [<parent>,<child>] format.
	 */
	protected $theme;

	public function __construct($name, ButlerInterface $butler = null) {
		parent::__construct($name);
		$this->butler = $butler ?: new WPBootsrapButler();
	}

	/**
	 * Returns an array containing the names of the suites the command will scaffold.
	 *
	 * @return array
	 */
	public static function getScaffoldedSuitesNames() {
		return ['acceptance', 'functional', 'integration', 'unit'];
	}

	public function getDescription() {
		return "Sets up a WordPress CodeCeption testing environment.";
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		if ($input->getOption('namespace')) {
			$this->namespace = trim($input->getOption('namespace'), '\\') . '\\';
		}

		if ($input->getOption('actor')) {
			$this->actorSuffix = $input->getOption('actor');
		}

		$path = $input->getArgument('path');

		if ($input->getOption('type') === 'theme') {
			$themeOption = $input->getOption('theme');

			if (empty($themeOption)) {
				throw new \RuntimeException('When the `type` option is set to `theme` the `theme` option must be set.');
			}

			$this->theme = strpos($themeOption, ',') !== 0 ? preg_split('/\\s*,\\s*/', $themeOption) : [$themeOption];
		}


		if (!(empty($path) || is_dir($path))) {
			$output->writeln("<error>\nDirectory '$path' does not exist\n</error>");
			return;
		}

		$realpath = realpath($path);
		chdir($path);

		if (file_exists('codeception.yml')) {
			$output->writeln("<error>\nProject is already initialized in '$path'\n</error>");
			return;
		}

		$output = $this->decorateOutput($output);

		$output->wrapAt(120);

		if ($input->getOption('interactive')) {
			$output->writeln("<info>This script will help you to set up a WordPress plugin or theme tests using wp-browser and Codeception. If this is the first time you do it take your time to read the notes for each question.</info>");
			$output->writeln("\n");

			$this->userConfig = $this->butler->askQuestions($this->getHelper('question'), $input, $output);
		} else {
			$this->userConfig = $this->fillUserConfigFromArgs($input);
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


		if ($input->getOption('interactive')) {
			$this->scaffoldBaseTestsAdvice($output);
		}
	}

	/**
	 * @param OutputInterface $output
	 *
	 * @return OutputInterface|WrappingOutput
	 */
	protected function decorateOutput(OutputInterface $output) {
		$output = new WrappingOutput($output);
		return $output;
	}

	public function createGlobalConfig() {
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
	protected function setupSuites(OutputInterface $output) {
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

	protected function createIntegrationSuite($actor = 'Integration') {
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
	protected function getIntegrationSuiteConfig($actor) {
		$className = $actor . $this->actorSuffix;
		$defaults = [
			'actor' => $actor,
			'className' => $className,
			'namespace' => $this->namespace,
		];

		$wploaderDefaults = $this->getWploaderDefaults();

		$userConfig = $this->userConfig;
		if (!empty($this->userConfig['usingIntegrationDatabase'])) {
			$userConfig['dbName'] = $this->userConfig['integrationDbName'];
			$userConfig['dbUser'] = $this->userConfig['integrationDbUser'];
			$userConfig['dbPassword'] = $this->userConfig['integrationDbPassword'];
			$userConfig['integrationTablePrefix'] = $this->userConfig['integrationTablePrefix'];
		}

		$settings = array_merge($defaults, $wploaderDefaults, $userConfig);

		if (!empty($this->theme)) {
			return (new IntegrationSuiteThemeConfig($settings))->produce();
		}
		return (new IntegrationSuiteConfig($settings))->produce();
	}

	protected function getWploaderDefaults() {
		$wploaderDefaults = [
			'wpRootFolder' => '/var/www/wordpress',
			'dbName' => 'wordpress-tests',
			'dbHost' => 'localhost',
			'dbUser' => 'root',
			'dbPassword' => '',
			'integrationTablePrefix' => 'int_',
			'domain' => 'wp.local',
			'adminEmail' => 'admin@wp.local',
			'plugins' => Yaml::dump(['hello.php'], 0)
		];

		if (!empty($this->theme)) {
			$wploaderDefaults['theme'] = count($this->theme) > 1 ? Yaml::dump($this->theme, 0) : reset($this->theme);
		}

		return $wploaderDefaults;
	}

	protected function createFunctionalSuite($actor = 'Functional') {
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
	protected function getFunctionalSuiteConfig($actor) {
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

	/**
	 * @return array
	 */
	protected function getWpdbConfigDefaults() {
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
	protected function getWordpressConfigDefaults() {
		$wordpressDefaults = [
			'wpRootFolder' => '/var/www/wordpress',
			'adminUsername' => 'admin',
			'adminPassword' => 'password',
		];
		return $wordpressDefaults;
	}

	protected function createAcceptanceSuite($actor = 'Acceptance') {
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
	protected function getAcceptanceSuiteConfig($actor) {
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

	/**
	 * @return array
	 */
	protected function getWpbrowserDefaults() {
		$wpbrowserDefaults = [
			'url' => 'http://wp.local',
			'adminUsername' => 'admin',
			'adminPassword' => 'password',
			'adminPath' => '/wp-admin',
		];
		return $wpbrowserDefaults;
	}

	private function scaffoldBaseTestsAdvice(OutputInterface $output) {
		$dumpPath = codecept_data_dir('dump.sql');
		$wpPath = $this->userConfig['wpRootFolder'];

		$output->writeln("\n");
		$output->writeln('<info>Generate your first unit test running:</info>');
		$output->writeln("\t<fg=blue>wpcept generate:test unit Sample</>");
		$output->writeln('<info>Generate your first integration test running:</info>');
		$output->writeln("\t<fg=blue>wpcept generate:wpunit integration Sample</>");
		$output->writeln('<info>Generate your first functional test running:</info>');
		$output->writeln("\t<fg=blue>wpcept generate:cest functional Sample</>");
		$output->writeln('<info>Generate your first acceptance test running:</info>');
		$output->writeln("\t<fg=blue>wpcept generate:cept acceptance Sample</>");
		$output->writeln("\n");
		$output->writeln('<info>If you haven\'t done it yet it\'s good practice to set up the WordPress installation that will be used to run the acceptance and functional tests to a pristine initial state and dump its database. If you are testing a plugin this could mean activating it and any additional plugin it might require and one of WordPress default themes; if you are testing a theme this could mean activating the theme and any plugin it might require to work.</info>');
		$output->writeln("<info>When you feel like the initial state is ok dump the local WordPress installation database using a GUI tool like SequelPro (https://www.sequelpro.com/) or a CLI tool like wp-cli (http://wp-cli.org/).</info>");
		$output->writeln("<info>If you have installed wp-cli use this command to dump the database:</info>");
		$output->writeln("\t<fg=blue>wp export dump $dumpPath --path=$wpPath</>");
	}

	protected function configure() {
		parent::configure();
		$this->addOption('no-build', null, InputOption::VALUE_NONE, 'Don\'t build after the bootstrap');
		$this->addOption('interactive', 'i', InputOption::VALUE_NONE, 'Interactive bootstrap');
		$this->addOption('dbHost', null, InputOption::VALUE_REQUIRED, 'A preset database host', 'localhost');
		$this->addOption('dbName', null, InputOption::VALUE_REQUIRED, 'A preset database name', 'database');
		$this->addOption('dbUser', null, InputOption::VALUE_REQUIRED, 'A preset database user', 'root');
		$this->addOption('dbPassword', null, InputOption::VALUE_REQUIRED, 'A preset database password', '');
		$this->addOption('tablePrefix', null, InputOption::VALUE_REQUIRED, 'A preset table prefix', 'wp_');
		$this->addOption('integrationTablePrefix', null, InputOption::VALUE_REQUIRED, 'A preset table prefix for the table used by integration tests', 'int_');
		$this->addOption('url', null, InputOption::VALUE_REQUIRED, 'A preset site URL', 'http://wp.dev');
		$this->addOption('wpRootFolder', null, InputOption::VALUE_REQUIRED, 'A preset WordPress root folder', '/var/www/wp');
		$this->addOption('adminUsername', null, InputOption::VALUE_REQUIRED, 'A preset administrator username', 'admin');
		$this->addOption('adminPassword', null, InputOption::VALUE_REQUIRED, 'A preset administratore password', 'password');
		$this->addOption('adminPath', null, InputOption::VALUE_REQUIRED, 'A preset administration area path', '/wp-admin');
		$this->addOption('type', null, InputOption::VALUE_REQUIRED,
			'The type of the component that will be tested ("plugin" or "theme"), def. to "plugin". If set to "theme" the "theme" option is required.',
			'');
		$this->addOption('theme', null, InputOption::VALUE_REQUIRED,
			'The slug of the theme that should be tested, e.g. "my-theme" or "parent-theme,child-theme" to test a child theme.', '');
		$this->addOption('plugins', null, InputOption::VALUE_REQUIRED, 'A preset list of plugins (comma separated slugs list)', '');
	}

	protected function fillUserConfigFromArgs(InputInterface $input) {
		$config = $this->getDefinition()->getOptionDefaults();
		$legit = [
			'dbHost',
			'dbName',
			'dbUser',
			'dbPassword',
			'tablePrefix',
			'integrationTablePrefix',
			'url',
			'wpRootFolder',
			'adminUsername',
			'adminPassword',
			'adminPath',
			'type',
			'theme',
			'plugins',
		];
		$config = array_intersect_key($config, array_combine($legit, $legit));
		array_walk($config, function (&$value, $name) use ($input) {
			$value = $input->getOption($name);
		});

		return $config;
	}
}
