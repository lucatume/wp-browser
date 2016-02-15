<?php

namespace Codeception\Command;

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
            $this->conditionalFileWrite($output, '.gitignore', 'tests/_log/*' );
        }
    }

    protected function setupSuites( OutputInterface $output ) {
        $this->createUnitSuite();
        $output->writeln("tests/unit created                 <- unit tests");
        $output->writeln("tests/unit.suite.yml written       <- unit tests suite configuration");
        $this->createWpunitSuite();
        $output->writeln("tests/wpunit created                 <- WordPress unit tests");
        $output->writeln("tests/wpunit.suite.yml written       <- WordPress unit tests suite configuration");
        $this->createServiceSuite();
        $output->writeln("tests/service created           <- service tests");
        $output->writeln("tests/service.suite.yml written <- service tests suite configuration");
        $this->createUiSuite();
        $output->writeln("tests/ui created           <- ui tests");
        $output->writeln("tests/ui.suite.yml written <- ui tests suite configuration");
    }

    protected function conditionalFileWrite(OutputInterface $output, $file, $contents) {
        $fileContents = file_get_contents($file);
        if (!preg_match('/^' . preg_quote($contents, '/') . '/ims', $fileContents)) {
            file_put_contents($file, "\n{$contents}", FILE_APPEND);
            $output->writeln("{$contents} was added to {$file}");
        }
    }
}
