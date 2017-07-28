<?php

namespace Codeception\Template;

use Codeception\Exception\ModuleConfigException;
use Symfony\Component\Yaml\Yaml;
use tad\WPBrowser\Template\Data;

class Wpbrowser extends Bootstrap {

	/**
	 * @var bool
	 */
	protected $quiet = false;

	/**
	 * @var bool
	 */
	protected $noInteraction = false;

	public function setup($interactive = true) {
		$this->checkInstalled($this->workDir);

		$input = $this->input;

		$this->quiet         = $this->input->getOption('quiet');
		$this->noInteraction = $this->input->getOption('no-interaction');

		if ($this->noInteraction || $this->quiet) {
			$interactive = false;
		}

		if ($input->getOption('namespace')) {
			$this->namespace = trim($input->getOption('namespace'), '\\') . '\\';
		}

		if ($input->hasOption('actor') && $input->getOption('actor')) {
			$this->actorSuffix = $input->getOption('actor');
		}

		$this->say("<fg=white;bg=magenta> Bootstrapping Codeception for WordPress </fg=white;bg=magenta>\n");

		$this->createGlobalConfig();
		$this->say("File codeception.yml created       <- global configuration");

		$this->createDirs();

		if ($input->hasOption('empty') && $input->getOption('empty')) {
			return;
		}

		if ($interactive === null) {
			$this->say();
			$interactive = $this->ask('Would you like to set up the suites interactively now?', 'yes');
			$this->say(" --- ");
			$this->say();
			$interactive = preg_match('/^(n|N)/', $interactive) ? false : true;
		}

		$installationData = $this->getInstallationData($interactive);

		try {
			$this->createUnitSuite();
			$this->say("tests/unit created                 <- unit tests");
			$this->say("tests/unit.suite.yml written       <- unit tests suite configuration");
			$this->createWpUnitSuite(ucwords($installationData['wpunitSuite']), $installationData);
			$this->say("tests/{$installationData['wpunitSuiteSlug']} created               <- WordPress unit and integration tests");
			$this->say("tests/{$installationData['wpunitSuiteSlug']}.suite.yml written     <- WordPress unit and integration tests suite configuration");
			$this->createFunctionalSuite(ucwords($installationData['functionalSuite']), $installationData);
			$this->say("tests/{$installationData['functionalSuiteSlug']} created           <- {$installationData['functionalSuiteSlug']} tests");
			$this->say("tests/{$installationData['functionalSuiteSlug']}.suite.yml written <- {$installationData['functionalSuiteSlug']} tests suite configuration");
			$this->createAcceptanceSuite(ucwords($installationData['acceptanceSuite']), $installationData);
			$this->say("tests/{$installationData['acceptanceSuiteSlug']} created           <- {$installationData['acceptanceSuiteSlug']} tests");
			$this->say("tests/{$installationData['acceptanceSuiteSlug']}.suite.yml written <- {$installationData['acceptanceSuiteSlug']} tests suite configuration");
		} catch (ModuleConfigException $e) {
			$this->sayWarning('Something is not ok in the modules configurations: check your answers and try the initialization again.');

			return;
		}

		$this->say(" --- ");
		$this->say();
		if ($interactive) {
			$this->saySuccess("Codeception is installed for {$installationData['acceptanceSuiteSlug']}, {$installationData['functionalSuiteSlug']}, and WordPress unit testing");
		} else {
			$this->saySuccess("Codeception has created the files for the {$installationData['acceptanceSuiteSlug']}, {$installationData['functionalSuiteSlug']}, WordPress unit and unit suites but the modules are not activated");
		}
		$this->say('Some commands have been added in he Codeception configuration file: check them out using <comment>codecept --help</comment>');
		$this->say(" --- ");
		$this->say();

		$this->say("<bold>Next steps:</bold>");
		$this->say('0. <bold>Create the databases used by the modules</bold>; wp-browser will not do it for you!');
		$this->say('1. <bold>Install and configure WordPress</bold> activating the theme and plugins you need to create a database dump in <comment>tests/_data/dump.sql</comment>');
		$this->say("2. Edit <bold>tests/{$installationData['acceptanceSuiteSlug']}.suite.yml</bold> to make sure WPDb and WPBrowser configurations match your local setup; change WPBrowser to WPWebDriver to enable browser testing");
		$this->say("3. Edit <bold>tests/{$installationData['functionalSuiteSlug']}.suite.yml</bold> to make sure WordPress and WPDb configurations match your local setup");
		$this->say("4. Edit <bold>tests/{$installationData['wpunitSuiteSlug']}.suite.yml</bold> to make sure WPLoader configuration matches your local setup");
		$this->say("5. Create your first {$installationData['acceptanceSuiteSlug']} tests using <comment>codecept g:cest {$installationData['acceptanceSuiteSlug']} WPFirst</comment>");
		$this->say("6. Write first test in <bold>tests/{$installationData['acceptanceSuiteSlug']}/WPFirstCest.php</bold>");
		$this->say("7. Run tests using: <comment>codecept run {$installationData['acceptanceSuiteSlug']}</comment>");
		$this->say(" --- ");
		$this->say();
		$this->sayWarning("Please note: due to WordPress extended use of globals and constants you should avoid running all the suites at the same time.");
		$this->say("Run each suite separately, like this: <comment>codecept run unit && codecept run {$installationData['wpunitSuiteSlug']}</comment>, to avoid problems.");
	}

	protected function say($message = '') {
		if ($this->quiet) {
			return;
		}
		parent::say($message);
	}

	public function createGlobalConfig() {
		$basicConfig = [
			'paths'        => [
				'tests'   => 'tests',
				'output'  => $this->outputDir,
				'data'    => $this->dataDir,
				'support' => $this->supportDir,
				'envs'    => $this->envsDir,
			],
			'actor_suffix' => 'Tester',
			'extensions'   => [
				'enabled'  => ['Codeception\Extension\RunFailed'],
				'commands' => $this->getAddtionalCommands(),
			],
		];

		$str = Yaml::dump($basicConfig, 4);
		if ($this->namespace) {
			$namespace = rtrim($this->namespace, '\\');
			$str       = "namespace: $namespace\n" . $str;
		}
		$this->createFile('codeception.yml', $str);
	}

	protected function getAddtionalCommands() {
		return [
			'Codeception\\Command\\GenerateWPUnit',
			'Codeception\\Command\\GenerateWPRestApi',
			'Codeception\\Command\\GenerateWPRestController',
			'Codeception\\Command\\GenerateWPRestPostTypeController',
			'Codeception\\Command\\GenerateWPAjax',
			'Codeception\\Command\\GenerateWPCanonical',
			'Codeception\\Command\\GenerateWPXMLRPC',
			'Codeception\\Command\\DbSnapshot',
			'tad\\Codeception\\Command\\SearchReplace',
		];
	}

	/**
	 * @param $interactive
	 *
	 * @return array
	 */
	protected function getInstallationData($interactive) {
		if (!$interactive) {
			$installationData = [
				'acceptanceSuite'     => 'acceptance',
				'functionalSuite'     => 'functional',
				'wpunitSuite'         => 'wpunit',
				'acceptanceSuiteSlug' => 'acceptance',
				'functionalSuiteSlug' => 'functional',
				'wpunitSuiteSlug'     => 'wpunit',
				'dbHost'              => 'localhost',
				'dbName'              => 'wp',
				'dbUser'              => 'root',
				'dbPassword'          => '',
				'tablePrefix'         => 'wp_',
				'url'                 => 'http://wp.localhost',
				'adminUsername'       => 'admin',
				'adminPassword'       => 'password',
				'wpAdminPath'         => '/wp-admin',
				'wpRootFolder'        => '/var/www/html',
				'wploaderDbName'      => 'wpTests',
				'wploaderDbHost'      => 'localhost',
				'wploaderDbUser'      => 'root',
				'wploaderDbPassword'  => '',
				'wploaderTablePrefix' => 'wp_',
				'urlHost'             => 'wp.localhost',
				'adminEmail'          => 'admin@wp.localhost',
				'title'               => 'WP Test',
				// deactivate all modules that could trigger exceptions when initialized with sudo values
				'activeModules'       => ['WPDb' => false, 'WordPress' => false, 'WPLoader' => false],
			];
		} else {
			$installationData = $this->askForInstallationData();
		}

		return $installationData;
	}

	protected function askForInstallationData() {
		$installationData = [
			'activeModules' => [
				'WPDb'      => true,
				'WPBrowser' => true,
				'WordPress' => true,
				'WPLoader'  => true,
			],
		];

		$installationData['acceptanceSuite'] = $this->ask('How would you like the acceptance suite to be called?', 'acceptance');
		$installationData['functionalSuite'] = $this->ask('How would you like the functional suite to be called?', 'functional');
		$installationData['wpunitSuite']     = $this->ask('How would you like the WordPress unit and integration suite to be called?', 'wpunit');

		$installationData['acceptanceSuiteSlug'] = strtolower($installationData['acceptanceSuite']);
		$installationData['functionalSuiteSlug'] = strtolower($installationData['functionalSuite']);
		$installationData['wpunitSuiteSlug']     = strtolower($installationData['wpunitSuite']);

		$this->say('---');
		$this->say();

		$this->say('WPLoader and WordPress modules need to access the WordPress files to work');
		$installationData['wpRootFolder'] = $this->ask("Where is WordPress installed?", '/var/www/wp');
		$installationData['wpAdminPath']  = $this->ask('What is the path, relative to WordPress root folder, of the admin area?', '/wp-admin');
		$installationData['wpAdminPath']  = '/' . trim($installationData['wpAdminPath'], '/');
		$this->say('The WPDb module needs the database details to access the database used by WordPress');
		$installationData['dbName']      = $this->ask("What's the name of the database used by the WordPress installation?", 'wp');
		$installationData['dbHost']      = $this->ask("What's the host of the database used by the WordPress installation?", 'localhost');
		$installationData['dbUser']      = $this->ask("What's the user of the database used by the WordPress installation?", 'root');
		$installationData['dbPassword']  = $this->ask("What's the password of the database used by the WordPress installation?", '');
		$installationData['tablePrefix'] = $this->ask("What's the table prefix of the database used by the WordPress installation?", 'wp_');
		$this->say('WPLoader will reinstall a fresh WordPress installation before the tests; as such it needs the details you would typically provide when installing WordPress from scratch');
		$this->say();
		$this->sayWarning('WPLoader should be configured to run on a dedicated database!');
		$this->say();
		$installationData['wploaderDbName']      = $this->ask("What's the name of the database WPLoader should use?", 'wpTests');
		$installationData['wploaderDbHost']      = $this->ask("What's the host of the database WPLoader should use?", 'localhost');
		$installationData['wploaderDbUser']      = $this->ask("What's the user of the database WPLoader should use?", 'root');
		$installationData['wploaderDbPassword']  = $this->ask("What's the password of the database WPLoader should use?", '');
		$installationData['wploaderTablePrefix'] = $this->ask("What's the table prefix of the database WPLoader should use?", 'wp_');
		$installationData['url']                 = $this->ask("What's the URL the WordPress installation?", 'http://wp.localhost');
		$installationData['url']                 = rtrim($installationData['url'], '/');
		$url                                     = parse_url($installationData['url']);
		$installationData['urlScheme']           = empty($url['scheme']) ? 'http' : $url['scheme'];
		$installationData['urlHost']             = empty($url['host']) ? 'example.com' : $url['host'];
		$installationData['urlPort']             = empty($url['port']) ? '' : ':' . $url['port'];
		$installationData['urlPath']             = empty($url['path']) ? '' : $url['path'];
		$adminEmailCandidate                     = "admin@{$installationData['urlHost']}";
		$installationData['adminEmail']          = $this->ask("What's the email of the WordPress site administrator?", $adminEmailCandidate);
		$installationData['title']               = $this->ask("What's the title of the WordPress site?", 'Test');
		$installationData['adminUsername']       = $this->ask('What is the login of the administrator user?', 'admin');
		$installationData['adminPassword']       = $this->ask('What is the password of the administrator user?', 'password');
		//			plugins: ['hello.php', 'my-plugin/my-plugin.php']
		$sut                         = $this->ask("Are you testing a plugin or a theme?", 'plugin');
		$installationData['plugins'] = [];
		if ($sut === 'plugin') {
			$installationData['mainPlugin'] = $this->ask('What is the <comment>folder/plugin.php</comment> name of the plugin?',
				'my-plugin/my-plugin.php');
		} else {
			$isChildTheme = $this->ask('Are you developing a child theme?', 'no');
			if (preg_match('/^(y|Y)/', $isChildTheme)) {
				$installationData['parentTheme'] = $this->ask('What is the slug of the parent theme?', 'twentyseventeen');
			}
			$installationData['theme'] = $this->ask('What is the slug of the theme?', 'my-theme');

		}
		$activateFurtherPlugins = $this->ask('Does your plugin or theme needs additional plugins to be activated to work?', 'no');

		if (preg_match('/^(y|Y)/', $activateFurtherPlugins)) {
			do {
				$plugin                        = $this->ask('Please enter the plugin <comment>folder/plugin.php</comment> (leave blank when done)',
					'');
				$installationData['plugins'][] = $plugin;
			} while (!empty($plugin));
		}

		$installationData['plugins'] = array_filter($installationData['plugins']);
		if (!empty($installationData['mainPlugin'])) {
			$installationData['plugins'] = $installationData['mainPlugin'];
		}

		return $installationData;
	}

	protected function createWpUnitSuite($actor = 'Wpunit', array $installationData = []) {
		$installationData = new Data($installationData);
		$WPLoader         = !empty($installationData['activeModules']['WPLoader']) ? '- WPLoader' : '# - WPLoader';
		$suiteConfig      = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for unit or integration tests that require WordPress functions and classes.

actor: $actor{$this->actorSuffix}
modules:
    enabled:
        {$WPLoader}
        - \\{$this->namespace}Helper\\$actor
    config:
        WPLoader:
            wpRootFolder: "{$installationData['wpRootFolder']}"
            dbName: "{$installationData['wploaderDbName']}"
            dbHost: "{$installationData['wploaderDbHost']}"
            dbUser: "{$installationData['wploaderDbUser']}"
            dbPassword: "{$installationData['wploaderDbPassword']}"
            tablePrefix: "{$installationData['wploaderTablePrefix']}"
            domain: "{$installationData['urlHost']}{$installationData['urlPort']}"
            adminEmail: "{$installationData['adminEmail']}"
            title: "{$installationData['title']}"
EOF;

		if (!empty($installationData['theme'])) {
			$theme       = empty($installationData['parentTheme']) ?
				$installationData['theme']
				: "[{$installationData['parentTheme']}, {$installationData['theme']}]";
			$suiteConfig .= <<<EOF
            
            theme: {$theme}
EOF;
		}

		$plugins     = $installationData['plugins'];
		$plugins     = "'" . implode("', '", (array) $plugins) . "'";
		$suiteConfig .= <<< EOF
        
            plugins: [{$plugins}]
            activatePlugins: [{$plugins}]
EOF;

		$this->createSuite('wpunit', $actor, $suiteConfig);
	}

	protected function createFunctionalSuite($actor = 'Functional', array $installationData = []) {
		$installationData = new Data($installationData);
		$WPDb             = !empty($installationData['activeModules']['WPDb']) ? '- WPDb' : '# - WPDb';
		$WordPress        = !empty($installationData['activeModules']['WordPress']) ? '- WordPress' : '# - WordPress';
		$suiteConfig      = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for {$installationData['functionalSuiteSlug']} tests
# Emulate web requests and make WordPress process them

actor: $actor{$this->actorSuffix}
modules:
    enabled:
        {$WPDb}
        {$WordPress}
        - Asserts
        - \\{$this->namespace}Helper\\{$actor}
    config:
        WPDb:
            dsn: 'mysql:host={$installationData['dbHost']};dbname={$installationData['dbName']}'
            user: '{$installationData['dbUser']}'
            password: '{$installationData['dbPassword']}'
            dump: 'tests/_data/dump.sql'
            populate: true
            cleanup: true
            url: '{$installationData['url']}'
            urlReplacement: true
            tablePrefix: '{$installationData['tablePrefix']}'
        WordPress:
            depends: WPDb
            wpRootFolder: '{$installationData['wpRootFolder']}'
            adminUsername: '{$installationData['adminUsername']}'
            adminPassword: '{$installationData['adminPassword']}'
            adminPath: '{$installationData['wpAdminPath']}'
EOF;
		$this->createSuite('functional', $actor, $suiteConfig);
	}

	protected function createAcceptanceSuite($actor = 'Acceptance', array $installationData = null) {
		$installationData = new Data($installationData);
		$WPDb             = !empty($installationData['activeModules']['WPDb']) ? '- WPDb' : '# - WPDb';
		$WPBrowser        = !empty($installationData['activeModules']['WPBrowser']) ? '- WPBrowser' : '# - WPBrowser';
		$suiteConfig      = <<<EOF
# Codeception Test Suite Configuration
#
# Suite for {$installationData['acceptanceSuiteSlug']} tests.
# Perform tests in browser using the WPWebDriver or WPBrowser.
# Use WPDb to set up your initial database fixture.
# If you need both WPWebDriver and WPBrowser tests - create a separate suite.

actor: $actor{$this->actorSuffix}
modules:
    enabled:
        {$WPDb}
        {$WPBrowser}
        - \\{$this->namespace}Helper\\{$actor}
    config:
        WPDb:
            dsn: 'mysql:host={$installationData['dbHost']};dbname={$installationData['dbName']}'
            user: '{$installationData['dbUser']}'
            password: '{$installationData['dbPassword']}'
            dump: 'tests/_data/dump.sql'
            populate: true #import the dump before the tests
            cleanup: true #import the dump between tests
            url: '{$installationData['url']}'
            urlReplacement: true #replace the hardcoded dump URL with the one above
            tablePrefix: '{$installationData['tablePrefix']}'
        WPBrowser:
            url: '{$installationData['url']}'
            adminUsername: '{$installationData['adminUsername']}'
            adminPassword: '{$installationData['adminPassword']}'
            adminPath: '{$installationData['wpAdminPath']}'
EOF;
		$this->createSuite('acceptance', $actor, $suiteConfig);
	}

	protected function getDefaultInstallationData() {
		return [];
	}
}