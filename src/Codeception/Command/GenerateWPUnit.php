<?php

namespace Codeception\Command;

use Codeception\CustomCommandInterface;
use Codeception\Lib\Generator\WPUnit;
use Codeception\Lib\Generator\WPUnit as WPUnitGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates skeleton for unit test as in classical PHPUnit.
 *
 * * `codecept g:wpunit unit UserTest`
 * * `codecept g:wpunit unit User`
 * * `codecept g:wpunit unit "App\User`
 *
 */
class GenerateWPUnit extends GenerateTest implements CustomCommandInterface
{

    use Shared\FileSystem;
    use Shared\Config;

    /**
     * returns the name of the command
     *
     * @return string
     */
    public static function getCommandName()
    {
        return "generate:wpunit";
    }

    public function getDescription()
    {
        return 'Generates a WPTestCase: a WP_UnitTestCase extension with Codeception super-powers.';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('class');

        $config = $this->getSuiteConfig($suite);

        $filename = $this->buildPath($config['path'], $class);

        $gen = $this->getGenerator($config, $class);

        $res = $this->createFile($filename, $gen->produce());

        if (! $res) {
            $output->writeln("<error>Test $filename already exists</error>");
            exit;
        }

        $output->writeln("<info>Test was created in $filename</info>");
    }

    protected function buildPath($path, $class)
    {
        $className = $this->getShortClassName($class);
        $path = $this->createDirectoryFor($path, $class);

        $filename = $this->completeSuffix($className, 'Test');

        return $path . $filename;
    }

    /**
     * @param $config
     * @param $class
     *
     * @return WPUnitGenerator
     */
    protected function getGenerator($config, $class)
    {
        return new WPUnit($config, $class, '\\Codeception\\TestCase\\WPTestCase');
    }

    protected function configure()
    {
        $this->setDefinition([

            new InputArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put'),
            new InputArgument('class', InputArgument::REQUIRED, 'class name'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ]);
        parent::configure();
    }
}
