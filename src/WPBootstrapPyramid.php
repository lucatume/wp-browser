<?php

namespace Codeception\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class WPBootstrapPyramid extends WPBootstrap
{

    public function getDescription()
    {
        return "Sets up a WordPress testing environment using the test pyramid suite organization.";
    }

    protected function createServiceSuite($actor = 'Service')
    {
        $suiteConfig = $this->getFunctionalSuiteConfig($actor);

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for service (integration) tests.\n";
        $str .= "# Emulate web requests and make application process them.\n";
        $str .= Yaml::dump($suiteConfig, 2);
        $this->createSuite('service', $actor, $str);
    }

    protected function createUiSuite($actor = 'UI')
    {
        $suiteConfig = $this->getAcceptanceSuiteConfig($actor);

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for UI (acceptance) tests.\n";
        $str .= "# perform tests in browser using WPBrowser or WPWebDriver modules.\n";

        $str .= Yaml::dump($suiteConfig, 5);
        $this->createSuite('ui', $actor, $str);
    }

    protected function compatibilitySetup( OutputInterface $output ) {
        $this->actorSuffix = 'Guy';

        $this->logDir = 'tests/_log';
        $this->helperDir = 'tests/_helpers';

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
            file_put_contents('tests/_log/.gitignore', '');
            file_put_contents('.gitignore', file_get_contents('.gitignore') . "\ntests/_log/*");
            $output->writeln("tests/_log was added to .gitignore");
        }
    }

    protected function setup( OutputInterface $output ) {
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
