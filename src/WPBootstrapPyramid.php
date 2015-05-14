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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
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

        $output->writeln("<fg=white;bg=magenta> Initializing Codeception in " . $realpath . " </fg=white;bg=magenta>\n");

        $this->createGlobalConfig();
        $output->writeln("File codeception.yml created       <- global configuration");
        $this->createDirs();

        if (!$input->getOption('empty')) {
            $this->createUnitSuite();
            $output->writeln("tests/unit created                 <- unit tests");
            $output->writeln("tests/unit.suite.yml written       <- unit tests suite configuration");
            $this->createServiceSuite();
            $output->writeln("tests/service created           <- service tests");
            $output->writeln("tests/service.suite.yml written <- service tests suite configuration");
            $this->createUiSuite();
            $output->writeln("tests/ui created           <- ui tests");
            $output->writeln("tests/ui.suite.yml written <- ui tests suite configuration");
        }

        $output->writeln(" --- ");
        $this->ignoreFolderContent('tests/_output');

        file_put_contents('tests/_bootstrap.php', "<?php\n// This is global bootstrap for autoloading\n");
        $output->writeln("tests/_bootstrap.php written <- global bootstrap file");

        $output->writeln("<info>Building initial {$this->actorSuffix} classes</info>");
        $this->getApplication()->find('build')->run(new ArrayInput(['command' => 'build']), $output);

        $output->writeln("<info>\nBootstrap is done. Check out " . $realpath . "/tests directory</info>");
    }
}
