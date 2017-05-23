<?php

namespace Codeception\Template;

use Symfony\Component\Yaml\Yaml;

class Wpbrowser extends Bootstrap {

	public function setup() {
		$this->checkInstalled($this->workDir);

		$input = $this->input;
		if ($input->getOption('namespace')) {
			$this->namespace = trim($input->getOption('namespace'), '\\') . '\\';
		}

		if ($input->hasOption('actor') && $input->getOption('actor')) {
			$this->actorSuffix = $input->getOption('actor');
		}

		$this->say(
			"<fg=white;bg=magenta> Bootstrapping wp-browser on top of Codeception </fg=white;bg=magenta>\n"
		);

		$this->createGlobalConfig();
		$this->say("File codeception.yml created       <- global configuration");

		$this->createDirs();

		if ($input->hasOption('empty') && $input->getOption('empty')) {
			return;
		}

		$this->createUnitSuite();
		$this->say("tests/unit created                 <- unit tests");
		$this->say("tests/unit.suite.yml written       <- unit tests suite configuration");
		$this->createFunctionalSuite();
		$this->say("tests/functional created           <- functional tests");
		$this->say("tests/functional.suite.yml written <- functional tests suite configuration");
		$this->createAcceptanceSuite();
		$this->say("tests/acceptance created           <- acceptance tests");
		$this->say("tests/acceptance.suite.yml written <- acceptance tests suite configuration");

		$this->say(" --- ");
		$this->say();
		$this->saySuccess('Codeception is installed for acceptance, functional, and unit testing');
		$this->say();

		$this->say("<bold>Next steps:</bold>");
		$this->say('1. Edit <bold>tests/acceptance.suite.yml</bold> to set url of your application. Change PhpBrowser to WebDriver to enable browser testing');
		$this->say("2. Edit <bold>tests/functional.suite.yml</bold> to enable a framework module. Remove this file if you don't use a framework");
		$this->say("3. Create your first acceptance tests using <comment>codecept g:cest acceptance First</comment>");
		$this->say("4. Write first test in <bold>tests/acceptance/FirstCest.php</bold>");
		$this->say("5. Run tests using: <comment>codecept run</comment>");
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
			$str = "namespace: $namespace\n" . $str;
		}
		$this->createFile('codeception.yml', $str);
	}

	protected function getAddtionalCommands() {
		return [
			'Codeception\\Command\\DbSnapshot',
			'Codeception\\Command\\GeneratePhpunitBootstrap',
			'Codeception\\Command\\GenerateWPAjax',
			'Codeception\\Command\\GenerateWPCanonical',
			'Codeception\\Command\\GenerateWPRestApi',
			'Codeception\\Command\\GenerateWPRestController',
			'Codeception\\Command\\GenerateWPRestPostTypeController',
			'Codeception\\Command\\GenerateWPUnit',
			'Codeception\\Command\\GenerateWPXMLRPC',
		];
	}
}