<?php

namespace lucatume\WPBrowser\Command;

use Codeception\Configuration;
use Codeception\CustomCommandInterface;
use Codeception\Exception\ConfigurationException;
use Exception;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Module\WPFilesystem;
use lucatume\WPBrowser\Module\WPLoader;
use lucatume\WPBrowser\Traits\ConfigurationReader;
use lucatume\WPBrowser\WordPress\CliProcess;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SuiteWpCLI extends Command implements CustomCommandInterface
{
    use ConfigurationReader;

    /**
     * @var string[]
     */
    private array $wpRootDirProviderModules = [
        WPLoader::class,
        'Codeception\\Module\\WPLoader',
        'WPLoader',
        WPFilesystem::class,
        'Codeception\\Module\\WPFilesystem',
        'WPFilesystem'
    ];

    public static function getCommandName(): string
    {
        return 'wp:cli';
    }

    public function getDescription(): string
    {
        return 'Runs a WP CLI command using a suite configuration.';
    }

    protected function configure()
    {
        $this
            ->addArgument('suite', InputArgument::REQUIRED, 'The suite to use to run the command.')
            ->addArgument(
                'wp-cli-command',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'The WP CLI command to run.'
            );
    }

    /**
     * @throws ConfigurationException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $suite = $input->getArgument('suite');

        if (!is_string($suite)) {
            throw new InvalidArgumentException('The suite argument must be a string.');
        }

        /** @var array<string> $command */
        $command = $input->getArgument('wp-cli-command');

        $globalConfiguration = Configuration::config();
        /** @var array<string,mixed> $suiteConfiguration */
        $suiteConfiguration = Configuration::suiteSettings($suite, $globalConfiguration);

        $wpRootDir = $this->readWpRootDir($suiteConfiguration);

        if (!$wpRootDir) {
            $wpRootDir = $this->readWpRootDir($globalConfiguration);
        }

        if (!$wpRootDir) {
            throw new InvalidArgumentException(
                'No module providing the WP root directory found (WPLoader or WPFilesystem).'
            );
        }

        $passthru = function (string $type, string $buffer) use ($output) {
            $output->write($buffer);
        };

        // Prepend `--path=<wpRootDir>` to the command, if not already present.
        if (!str_contains($command[0], '--path=')) {
            array_unshift($command, '--path=' . $wpRootDir);
        }

        // Do not change the `cwd` to make sure relative paths will keep working.
        return (new CliProcess($command))->run($passthru);
    }

    /**
     * @param array<string,mixed> $config
     */
    private function readWpRootDir(array $config): ?string
    {
        $matchingConfigs = $this->getConfigsForModules($config, $this->wpRootDirProviderModules);

        return array_reduce(
            $matchingConfigs,
            static function (?string $carry, array $config): ?string {
                return isset($config['wpRootFolder']) && is_string($config['wpRootFolder']) ?
                    $config['wpRootFolder']
                    : null;
            }
        );
    }
}
