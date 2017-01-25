<?php


use Codeception\Command\WPBootstrap;
use Ofbeaton\Console\Tester\QuestionTester;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;
use function tad\WPBrowser\Tests\Support\rrmdir;

class WPBootstrapTest extends \Codeception\Test\Unit {
	use QuestionTester;

	protected static $path;
	protected static $cwdBackup;
	/**
	 * @var string
	 */
	protected $acceptanceSuiteConfigFile = 'tests/acceptance.suite.yml';
	/**
	 * @var string
	 */
	protected $functionalSuiteConfigFile = 'tests/functional.suite.yml';
	/**
	 * @var Application
	 */
	protected $application;

	/**
	 * @var array
	 */
	protected $answerShiftReg = [];

	/**
	 * @var OutputInterface
	 */
	protected $outputInterface;

	/**
	 * @var InputInterface
	 */
	protected $inputInterface;
	/**
	 * @var \IntegrationTester
	 */
	protected $tester;

	/**
	 * @var array
	 */
	protected $asked = [];


	public static function setUpBeforeClass() {
		self::$cwdBackup = getcwd();
		self::$path = codecept_data_dir('folder-structures/wpbootstrap-test-root');
	}

	public static function tearDownAfterClass() {
		self::clean();
		chdir(self::$cwdBackup);
	}

	/**
	 * @test
	 * it should scaffold acceptance suite
	 */
	public function it_should_scaffold_acceptance_suite() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);
		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true
		]);

		$this->assertFileExists($this->testDir($this->acceptanceSuiteConfigFile));
	}

	/**
	 * @param Application $app
	 */
	protected function addCommand(Application $app) {
		$app->add(new WPBootstrap('bootstrap'));
	}

	protected function testDir($relative = '') {
		$frag = $relative ? '/' . ltrim($relative, '/') : '';
		return self::$path . $frag;
	}

	/**
	 * @test
	 * it should scaffold functional test suite
	 */
	public function it_should_scaffold_functional_test_suite() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);
		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true
		]);

		$this->assertFileExists($this->testDir($this->functionalSuiteConfigFile));
	}

	/**
	 * @test
	 * it should allow user to specify params through questions for functional suite
	 */
	public function it_should_allow_user_to_specify_params_through_questions_for_functional_suite() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$wpFolder = $this->getWpFolder();

		$this->mockAnswers($command, $this->getDefaultQuestionsAndAnswers($wpFolder));

		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true,
			'--interactive' => true
		]);

		$file = $this->testDir($this->functionalSuiteConfigFile);

		$this->assertFileExists($file);

		$fileContents = file_get_contents($file);

		$this->assertNotEmpty($fileContents);

		$decoded = Yaml::parse($fileContents);

		$this->assertContains('mysql:host=mysql', $decoded['modules']['config']['WPDb']['dsn']);
		$this->assertContains('dbname=wpFuncTests', $decoded['modules']['config']['WPDb']['dsn']);
		$this->assertEquals('notRoot', $decoded['modules']['config']['WPDb']['user']);
		$this->assertEquals('notRootPass', $decoded['modules']['config']['WPDb']['password']);
		$this->assertEquals('wordpress_', $decoded['modules']['config']['WPDb']['tablePrefix']);
		$this->assertEquals('http://some.dev', $decoded['modules']['config']['WPDb']['url']);
		$this->assertEquals($wpFolder, $decoded['modules']['config']['WordPress']['wpRootFolder']);
		$this->assertEquals('luca', $decoded['modules']['config']['WordPress']['adminUsername']);
		$this->assertEquals('dadada', $decoded['modules']['config']['WordPress']['adminPassword']);
	}

	/**
	 * @return \org\bovigo\vfs\vfsStreamDirectory
	 */
	protected function getWpFolder() {
		$root = vfsStream::setup();
		$wp = vfsStream::newDirectory('wp', 0777);
		$wpAdmin = vfsStream::newDirectory('wp-admin', 0777);
		$wpLoad = vfsStream::newFile('wp-load.php', 0777);
		$wpLoad->setContent('<?php //not-loading ?>');

		$wp->addChild($wpLoad);
		$wp->addChild($wpAdmin);
		$root->addChild($wp);

		return $root->url() . '/wp';
	}

	/**
	 * @param $command
	 * @param $questionsAndAnswers
	 */
	protected function mockAnswers($command, $questionsAndAnswers) {
		$this->asked = new stdClass();
		$this->asked->questions = [];
		$asked = $this->asked;
		$this->mockQuestionHelper($command,
			function ($text, $order, Question $question) use ($questionsAndAnswers, &$asked) {
				foreach ($questionsAndAnswers as $key => $value) {
					if (preg_match('/' . $key . '/', $text)) {
						$asked->questions[] = $key;
						if (is_array($value)) {
							$index = !isset($this->answerShiftReg[$key]) ? 0 : $this->answerShiftReg[$key] + 1;
							if ($index > count($value) - 1) {
								$value = '';
							} else {
								$value = $value[$index];
								$this->answerShiftReg[$key] = $index;
							}
						}
						return $value;
					}
				}

				// no question matched, fail
				throw new PHPUnit_Framework_AssertionFailedError("No mocked answer for question '$text'");
			});
	}

	/**
	 * @param $wpFolder
	 *
	 * @return array
	 */
	protected function getDefaultQuestionsAndAnswers($wpFolder) {
		$questionsAndAnswers = [
			'database host' => 'mysql',
			'database name' => 'wpFuncTests',
			'database user' => 'notRoot',
			'database password' => 'notRootPass',
			'using.*different.*integration' => 'n',
			'(I|i)ntegration.*table prefix' => 'integration_',
			'table prefix.*' => 'wordpress_',
			'WordPress.*url' => 'http://some.dev',
			'WordPress.*domain' => 'some.dev',
			'WordPress.*root directory' => $wpFolder,
			'(A|a)dmin.*username' => 'luca',
			'(A|a)dmin.*password' => 'dadada',
			'(A|a)dmin.*email' => 'luca@theaveragedev.com',
			'path.*administration' => '/wp-admin',
			'(A|a)ctiv.*plugin(s)*' => '',
			'plugin.*or.*theme' => 'plugin',
			'theme.*slug' => 'my-theme'
		];
		return $questionsAndAnswers;
	}

	/**
	 * @test
	 * it should allow users to specify params through questions for acceptance suite
	 */
	public function it_should_allow_users_to_specify_params_through_questions_for_acceptance_suite() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$wpFolder = $this->getWpFolder();

		$this->mockAnswers($command, $this->getDefaultQuestionsAndAnswers($wpFolder));

		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true,
			'--interactive' => true
		]);

		$file = $this->testDir($this->acceptanceSuiteConfigFile);

		$this->assertFileExists($file);

		$fileContents = file_get_contents($file);

		$this->assertNotEmpty($fileContents);

		$decoded = Yaml::parse($fileContents);

		$this->assertContains('mysql:host=mysql', $decoded['modules']['config']['WPDb']['dsn']);
		$this->assertContains('dbname=wpFuncTests', $decoded['modules']['config']['WPDb']['dsn']);
		$this->assertEquals('notRoot', $decoded['modules']['config']['WPDb']['user']);
		$this->assertEquals('notRootPass', $decoded['modules']['config']['WPDb']['password']);
		$this->assertEquals('wordpress_', $decoded['modules']['config']['WPDb']['tablePrefix']);
		$this->assertEquals('http://some.dev', $decoded['modules']['config']['WPDb']['url']);
		$this->assertEquals('http://some.dev', $decoded['modules']['config']['WPBrowser']['url']);
		$this->assertEquals('luca', $decoded['modules']['config']['WPBrowser']['adminUsername']);
		$this->assertEquals('dadada', $decoded['modules']['config']['WPBrowser']['adminPassword']);
		$this->assertEquals('/wp-admin', $decoded['modules']['config']['WPBrowser']['adminPath']);
	}

	/**
	 * @test
	 * it should allow the database password to be empty
	 */
	public function it_should_allow_the_database_password_to_be_empty() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$wpFolder = $this->getWpFolder();

		$this->mockAnswers($command, array_merge(
				$this->getDefaultQuestionsAndAnswers($wpFolder),
				['database password' => '']
			)
		);

		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true,
			'--interactive' => true
		]);

		$file = $this->testDir($this->acceptanceSuiteConfigFile);

		$this->assertFileExists($file);

		$fileContents = file_get_contents($file);

		$this->assertNotEmpty($fileContents);

		$decoded = Yaml::parse($fileContents);

		$this->assertEquals('', $decoded['modules']['config']['WPDb']['password']);
	}

	public function differentFormatUrls() {
		return [
			['https://some.dev'],
			['http://localhost'],
			['https://localhost'],
			['http://localhost:8080'],
			['https://localhost:8080'],
			['http://192.168.1.22'],
			['https://192.168.1.22'],
			['http://192.168.1.22:8080'],
			['https://192.168.1.22:8080']
		];
	}

	/**
	 * @test
	 * it should allow for different format urls
	 * @dataProvider differentFormatUrls
	 */
	public function it_should_allow_for_different_format_urls($url) {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$wpFolder = $this->getWpFolder();

		$this->mockAnswers($command, array_merge(
				$this->getDefaultQuestionsAndAnswers($wpFolder),
				['WordPress.*url' => $url]
			)
		);

		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true,
			'--interactive' => true
		]);

		$file = $this->testDir($this->acceptanceSuiteConfigFile);

		$this->assertFileExists($file);

		$fileContents = file_get_contents($file);

		$this->assertNotEmpty($fileContents);

		$decoded = Yaml::parse($fileContents);

		$this->assertEquals($url, $decoded['modules']['config']['WPBrowser']['url']);
	}

	public function adminPathsFormats() {
		return [
			['/wp-admin', '/wp-admin'],
			['wp-admin', '/wp-admin'],
			['wp-admin/', '/wp-admin'],
			['/wp-admin/', '/wp-admin']
		];
	}

	/**
	 * @test
	 * it should allow different adminPath formats
	 * @dataProvider adminPathsFormats
	 */
	public function it_should_allow_different_admin_path_formats($adminPath, $expectedAdminPath) {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$wpFolder = $this->getWpFolder();

		$this->mockAnswers($command, array_merge(
				$this->getDefaultQuestionsAndAnswers($wpFolder),
				['path.*administration' => $adminPath]
			)
		);

		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true,
			'--interactive' => true
		]);

		$file = $this->testDir($this->acceptanceSuiteConfigFile);

		$this->assertFileExists($file);

		$fileContents = file_get_contents($file);

		$this->assertNotEmpty($fileContents);

		$decoded = Yaml::parse($fileContents);

		$this->assertEquals($expectedAdminPath, $decoded['modules']['config']['WPBrowser']['adminPath']);
	}

	/**
	 * @test
	 * it should scaffold the integration suite config file
	 */
	public function it_should_scaffold_the_integration_suite_config_file() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);
		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true
		]);

		$this->assertFileExists($this->testDir('tests/integration.suite.yml'));
	}

	/**
	 * @test
	 * it should allow setting integration configuration with user provided params
	 */
	public function it_should_allow_setting_integration_configuration_with_user_provided_params() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$wpFolder = $this->getWpFolder();

		$this->mockAnswers($command, $this->getDefaultQuestionsAndAnswers($wpFolder));

		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true,
			'--interactive' => true
		]);

		$file = $this->testDir('tests/integration.suite.yml');

		$this->assertFileExists($file);

		$fileContents = file_get_contents($file);

		$this->assertNotEmpty($fileContents);

		$decoded = Yaml::parse($fileContents);

		$this->assertEquals($wpFolder, $decoded['modules']['config']['WPLoader']['wpRootFolder']);
		$this->assertEquals('mysql', $decoded['modules']['config']['WPLoader']['dbHost']);
		$this->assertEquals('wpFuncTests', $decoded['modules']['config']['WPLoader']['dbName']);
		$this->assertEquals('notRoot', $decoded['modules']['config']['WPLoader']['dbUser']);
		$this->assertEquals('notRootPass', $decoded['modules']['config']['WPLoader']['dbPassword']);
		$this->assertEquals('integration_', $decoded['modules']['config']['WPLoader']['tablePrefix']);
		$this->assertEquals('some.dev', $decoded['modules']['config']['WPLoader']['domain']);
		$this->assertEquals('luca@theaveragedev.com', $decoded['modules']['config']['WPLoader']['adminEmail']);
	}

	/**
	 * @test
	 * it should allow user to specify plugins
	 */
	public function it_should_allow_user_to_specify_plugins() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$wpFolder = $this->getWpFolder();

		$expected = [
			'required/plugin.php',
			'acme/plugin-1.php',
			'acme/plugin-2.php'
		];
		$this->mockAnswers($command, array_merge(
			$this->getDefaultQuestionsAndAnswers($wpFolder),
			['(A|a)ctiv.*plugin(s)*' => $expected]
		));

		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true,
			'--interactive' => true
		]);

		$file = $this->testDir('tests/integration.suite.yml');

		$this->assertFileExists($file);

		$fileContents = file_get_contents($file);

		$this->assertNotEmpty($fileContents);

		$decoded = Yaml::parse($fileContents);

		$this->assertEquals($expected, $decoded['modules']['config']['WPLoader']['plugins']);
		$this->assertEquals($expected, $decoded['modules']['config']['WPLoader']['activatePlugins']);
	}

	/**
	 * @test
	 * it should allow using a different database for integration testing
	 */
	public function it_should_allow_using_a_different_database_for_integration_testing() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$wpFolder = $this->getWpFolder();

		$questionsAndAnswers = [
				'using.*different.*integration' => 'y',
				'(I|i)ntegration.*database name' => 'intTests',
				'(I|i)ntegration.*database user' => 'intUser',
				'(I|i)ntegration.*database password' => 'intPass',
				'(I|i)ntegration.*table prefix' => 'inte_',
			] + $this->getDefaultQuestionsAndAnswers($wpFolder);

		$this->mockAnswers($command, $questionsAndAnswers);

		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true,
			'--interactive' => true
		]);

		$file = $this->testDir('tests/integration.suite.yml');

		$this->assertFileExists($file);

		$fileContents = file_get_contents($file);

		$this->assertNotEmpty($fileContents);

		$decoded = Yaml::parse($fileContents);

		$this->assertEquals('intTests', $decoded['modules']['config']['WPLoader']['dbName']);
		$this->assertEquals('intUser', $decoded['modules']['config']['WPLoader']['dbUser']);
		$this->assertEquals('intPass', $decoded['modules']['config']['WPLoader']['dbPassword']);
		$this->assertEquals('inte_', $decoded['modules']['config']['WPLoader']['tablePrefix']);
	}

	/**
	 * @test
	 * it should allow to pass the type of the bootstrap with the --type option
	 */
	public function it_should_allow_to_pass_the_type_of_the_bootstrap_with_the_type_option() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$wpFolder = $this->getWpFolder();

		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true,
			'--type' => 'plugin'
		]);

		$file = $this->testDir('tests/integration.suite.yml');

		$this->assertFileExists($file);

		$fileContents = file_get_contents($file);

		$this->assertNotEmpty($fileContents);

		$decoded = Yaml::parse($fileContents);

		$this->assertNotContains('theme', $decoded['modules']['config']['WPLoader']);
	}

	/**
	 * @test
	 * it should default the bootstrap type to plugin when not specified
	 */
	public function it_should_default_the_bootstrap_type_to_plugin_when_not_specified() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$wpFolder = $this->getWpFolder();

		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true
		]);

		$file = $this->testDir('tests/integration.suite.yml');

		$this->assertFileExists($file);

		$fileContents = file_get_contents($file);

		$this->assertNotEmpty($fileContents);

		$decoded = Yaml::parse($fileContents);

		$this->assertNotContains('theme', $decoded['modules']['config']['WPLoader']);
	}

	/**
	 * @test
	 * it should require the theme value if the bootstrap type is set to theme
	 */
	public function it_should_require_the_theme_value_if_the_bootstrap_type_is_set_to_theme() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$wpFolder = $this->getWpFolder();

		$this->expectException(RuntimeException::class);

		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true,
			'--type' => 'theme'
		]);
	}

	/**
	 * @test
	 * it should set the theme slug when passed with option
	 */
	public function it_should_set_the_theme_slug_when_passed_with_option() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$wpFolder = $this->getWpFolder();

		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true,
			'--type' => 'theme',
			'--theme' => 'my-theme'
		]);

		$file = $this->testDir('tests/integration.suite.yml');

		$this->assertFileExists($file);

		$fileContents = file_get_contents($file);

		$this->assertNotEmpty($fileContents);

		$decoded = Yaml::parse($fileContents);

		$this->assertArrayHasKey('theme', $decoded['modules']['config']['WPLoader']);
		$this->assertEquals('my-theme', $decoded['modules']['config']['WPLoader']['theme']);
	}


	/**
	 * @test
	 * it should ask for the type in interactive mode if not passed as an option
	 */
	public function it_should_ask_for_the_type_in_interactive_mode_if_not_passed_as_an_option() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$this->mockAnswers($command, $this->getDefaultQuestionsAndAnswers($this->getWpFolder()));

		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true,
			'--interactive' => true
		]);

		$this->assertQuestionAsked('plugin.*or.*theme');
	}

	protected function assertQuestionAsked($expected) {
		$this->assertContains($expected, $this->asked->questions, "Question [{$expected}] was never asked.");
	}

	/**
	 * @test
	 * it should not ask for the type in interactive mode is passed as option
	 */
	public function it_should_not_ask_for_the_type_in_interactive_mode_is_passed_as_option() {
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$this->mockAnswers($command, $this->getDefaultQuestionsAndAnswers($this->getWpFolder()));

		$commandTester->execute([
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true,
			'--type' => 'theme',
			'--theme' => 'my-theme',
			'--interactive' => true
		]);

		$this->assertQuestionNotAsked('plugin or theme');
	}

	public function cliConfigValues() {
		return [
			[['integration', 'integration'], 'WPLoader', 'dbHost', 'some-host'],
			[['integration', 'integration'], 'WPLoader', 'dbName', 'some-db'],
			[['integration', 'integration'], 'WPLoader', 'dbUser', 'some-user'],
			[['integration', 'integration'], 'WPLoader', 'dbPassword', 'some-password'],
			[['acceptance', 'ui'], 'WPDb', 'tablePrefix', 'prefix_'],
			[['integration', 'integration'], 'WPLoader', 'integrationTablePrefix', 'prefix_', 'tablePrefix'],
			[['acceptance', 'ui'], 'WPDb', 'url', 'http://example.com'],
			[['integration', 'integration'], 'WPLoader', 'wpRootFolder', $this->testDir('/wordpress')],
			[['acceptance', 'ui'], 'WPBrowser', 'adminUsername', 'foo'],
			[['acceptance', 'ui'], 'WPBrowser', 'adminPassword', 'bar'],
			[['acceptance', 'ui'], 'WPBrowser', 'adminPath', '/sub-folder/wp-admin'],
			[['integration', 'integration'], 'WPLoader', 'theme', 'some-theme'],
			[['integration', 'integration'], 'WPLoader', 'plugins', 'foo/foo.php,bar/bar.php'],
		];
	}

	/**
	 * @test
	 * it should allow users to pass in options in non interactive mode
	 * @dataProvider cliConfigValues
	 */
	public function it_should_allow_users_to_pass_in_options_in_non_interactive_mode($suite, $module, $option, $value, $optionAlias = null) {
		$suite = $this instanceof WPBootstrapPyramidTest ? end($suite) : reset($suite);
		$app = new Application();
		$this->addCommand($app);
		$command = $app->find('bootstrap');
		$commandTester = new CommandTester($command);

		$args = [
			'command' => $command->getName(),
			'path' => $this->testDir(),
			'--no-build' => true,
			'--' . $option . '' => $value,
		];

		if ($option === 'theme') {
			$args['--type'] = 'theme';
		}

		$commandTester->execute($args);

		$suiteConfigFile = $this->testDir('tests/' . $suite . '.suite.yml');
		$this->assertFileExists($suiteConfigFile);
		$suiteConfig = Yaml::parse(file_get_contents($suiteConfigFile));
		$alias = is_null($optionAlias) ? $option : $optionAlias;
		$this->assertEquals($value, $suiteConfig['modules']['config'][$module][$alias]);
	}

	protected function assertQuestionNotAsked($unexpected) {
		$this->assertNotContains($unexpected, $this->asked->questions, "Question [{$unexpected}] was asked.");
	}

	protected function _before() {
		self::clean();
	}

	protected static function clean() {
		rrmdir(self::$path . '/tests');
		foreach (glob(self::$path . '/*.*') as $file) {
			unlink($file);
		}
	}

}
