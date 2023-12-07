<?php

namespace lucatume\WPBrowser\Command;

use Codeception\Command\GenerateTest;
use Codeception\Configuration;
use Codeception\CustomCommandInterface;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Lib\Generator\WPUnit as WPUnitGenerator;
use lucatume\WPBrowser\Module\WPLoader;
use lucatume\WPBrowser\TestCase\WPTestCase;
use lucatume\WPBrowser\Traits\ConfigurationReader;
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
    use ConfigurationReader;

    /**
     * Returns the name of the command.
     *
     * @return string The command name.
     */
    public static function getCommandName(): string
    {
        return "generate:wpunit";
    }

    /**
     * Returns the command description.
     *
     * @return string The command description.
     */
    public function getDescription(): string
    {
        return 'Generates a WPTestCase: a WP_UnitTestCase with Codeception super-powers.';
    }

    /**
     * Executes the command.
     *
     * @param InputInterface $input   The input.
     * @param OutputInterface $output The output.
     *
     * @return int Either the command return value or `null`.
     * @throws InvalidArgumentException If the suite argument is not a string.
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $suite = $input->getArgument('suite');

        if (!is_string($suite)) {
            throw new InvalidArgumentException('Suite argument must be a string.');
        }

        $modules = [WPLoader::class, 'WPLoader'];
        $globalConfig = Configuration::config();
        $suiteConfig = Configuration::suiteSettings($suite, $globalConfig);
        $wpLoaderConfigs = $this->getConfigsForModules($suiteConfig, $modules);
        if (count($wpLoaderConfigs) === 0) {
            $wpLoaderConfigs = $this->getConfigsForModules($globalConfig, $modules);
        }

        $wploaderCorrectLoad = count($wpLoaderConfigs)
            && array_reduce($wpLoaderConfigs, function ($carry, $config) {
                return $carry || empty($config['loadOnly']);
            }, false);

        if (!$wploaderCorrectLoad) {
            $output->writeln("<error>Test suite $suite does not use the WPLoader module or uses it in " .
                "`loadOnly` mode.</error>");
            $output->writeln("<error>WPUnit tests can only be generated for test suites that use the " .
                "WPLoader module.</error>");
            $output->writeln("<error>If you want to generate a test for a suite that does not use the " .
                "WPLoader module you can use the `codecept generate:test` command.</error>");
            return 1;
        }

        /** @var string $class */
        $class = $input->getArgument('class');

        /** @var array{namespace: string, actor: string, path: string} $config */
        $config = $this->getSuiteConfig($suite);

        $filename = $this->buildPath($config['path'], $class);

        $gen = $this->getGenerator($config, $class);

        if (!$this->createFile($filename, $gen->produce())) {
            $output->writeln("<error>Test $filename already exists</error>");
            return 1;
        }

        $output->writeln("<info>Test was created in $filename</info>");

        return 0;
    }

    /**
     * Returns the built path.
     *
     * @param string $path  The root path.
     * @param string $class The class to build the path for.
     *
     * @return string The built path.
     */
    protected function buildPath(string $path, string $class): string
    {
        $className = $this->getShortClassName($class);
        $path = $this->createDirectoryFor($path, $class);

        $filename = $this->completeSuffix($className, 'Test');

        return $path . $filename;
    }

    /**
     * Returns the configured generator.
     *
     * @param array{namespace: string, actor: string} $config The generator configuration.
     * @param string $class                                   The class to generate the test case for.
     *
     * @return WPUnitGenerator An instance of the test case code generator.
     */
    protected function getGenerator(array $config, string $class): WPUnitGenerator
    {
        return new WPUnitGenerator($config, $class, WPTestCase::class);
    }

    /**
     * Configures the generator.
     */
    protected function configure(): void
    {
        $this->setDefinition([
            new InputArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put'),
            new InputArgument('class', InputArgument::REQUIRED, 'class name'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ]);
        parent::configure();
    }
}
