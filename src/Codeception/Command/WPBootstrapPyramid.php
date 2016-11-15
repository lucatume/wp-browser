<?php

namespace Codeception\Command;

use Symfony\Component\Console\Output\OutputInterface;
use tad\WPBrowser\Console\Output\PyramidWordsOutput;
use tad\WPBrowser\Interactions\WPBootstrapPyramidButler;

class WPBootstrapPyramid extends WPBootstrap
{

	public function getDescription()
	{
		return "Sets up a WordPress testing environment using the test pyramid suite organization.";
	}

	protected function setupSuites(OutputInterface $output)
	{
		$this->createUnitSuite();
		$output->writeln("tests/unit created                    <- unit tests");
		$output->writeln("tests/unit.suite.yml written          <- unit tests suite configuration");
		$this->createIntegrationSuite();
		$output->writeln("tests/integration created             <- integration tests");
		$output->writeln("tests/integration.suite.yml written   <- integration tests suite configuration");
		$this->createServiceSuite();
		$output->writeln("tests/service created                 <- service tests");
		$output->writeln("tests/service.suite.yml written       <- service tests suite configuration");
		$this->createUiSuite();
		$output->writeln("tests/ui created                      <- ui tests");
		$output->writeln("tests/ui.suite.yml written            <- ui tests suite configuration");
	}

	protected function createServiceSuite($actor = 'Service')
	{
		$suiteConfig = $this->getFunctionalSuiteConfig($actor);

		$str = "# Codeception Test Suite Configuration\n\n";
		$str .= "# Suite for service tests.\n";
		$str .= "# Emulate web requests and make the WordPress application process them.\n";
		$str .= $suiteConfig;
		$this->createSuite('service', $actor, $str);
	}

	protected function createUiSuite($actor = 'UI')
	{
		$suiteConfig = $this->getAcceptanceSuiteConfig($actor);

		$str = "# Codeception Test Suite Configuration\n\n";
		$str .= "# Suite for UI tests.\n";
		$str .= "# Perform tests using or simulating a browser.\n";

		$str .= $suiteConfig;
		$this->createSuite('ui', $actor, $str);
	}

	protected function decorateOutput(OutputInterface $output)
	{
		$output = new PyramidWordsOutput($output);

		return parent::decorateOutput($output);
	}
}
