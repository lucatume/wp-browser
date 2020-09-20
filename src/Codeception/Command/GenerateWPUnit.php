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
     * Returns the name of the command.
     *
     * @return string The command name.
     */
    public static function getCommandName()
    {
        return "generate:wpunit";
    }

    /**
     * Returns the command description.
     *
     * @return string The command description.
     */
    public function getDescription()
    {
        return 'Generates a WPTestCase: a WP_UnitTestCase extension with Codeception super-powers.';
    }

    /**
     * Executes the command.
     *
     * @param InputInterface  $input The inputl
     * @param OutputInterface $output The output.
     *
     * @return int|null Either the command return value or `null`.
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        /** @var string $class */
        $class = $input->getArgument('class');

        $config = $this->getSuiteConfig($suite);

        $filename = $this->buildPath($config['path'], $class);

        $gen = $this->getGenerator($config, $class);

        $res = $this->createFile($filename, $gen->produce());

        if (! $res) {
            $output->writeln("<error>Test $filename already exists</error>");
            return 1;
        }

        $output->writeln("<info>Test was created in $filename</info>");

        return 0;
    }

    /**
     * Returns the built path.
     *
     * @param string $path The root path.
     * @param string $class The class to build the path for.
     *
     * @return string The built path.
     */
    protected function buildPath($path, $class)
    {
        $className = $this->getShortClassName($class);
        $path = $this->createDirectoryFor($path, $class);

        $filename = $this->completeSuffix($className, 'Test');

        return $path . $filename;
    }

    /**
     * Returns the configured generator.
     *
     * @param array<string,mixed> $config The generator configuration.
     * @param string $class The class to generate the test case for.
     *
     * @return WPUnitGenerator An instance of the test case code generator.
     */
    protected function getGenerator($config, $class)
    {
        return new WPUnit($config, $class, '\\Codeception\\TestCase\\WPTestCase');
    }

    /**
     * Configures the generator.
     *
     * @return void
     */
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
