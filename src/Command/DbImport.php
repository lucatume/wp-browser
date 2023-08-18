<?php

namespace lucatume\WPBrowser\Command;

use Codeception\CustomCommandInterface;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\WordPress\WpConfigFileException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class DbImport extends Command implements CustomCommandInterface
{
    public const INVALID_PATH = 1;
    public const DUMP_FILE_NOT_FOUND = 2;

    public static function getCommandName(): string
    {
        return 'wp:db:import';
    }

    public function getDescription(): string
    {
        return 'Exports the database used by a suite to a file.';
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'The path to the WordPress root directory.');
        $this->addArgument('dumpFilePath', InputArgument::REQUIRED, 'The path of the dump file to import.');
    }

    /**
     * @throws Throwable
     * @throws DbException
     * @throws WorkerException
     * @throws WpConfigFileException
     * @throws InstallationException
     * @throws ProcessException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');
        $dumpFilePath = $input->getArgument('dumpFilePath');

        if (!(is_string($path) && is_dir($path) && is_file($path . '/wp-load.php'))) {
            throw new InvalidArgumentException(
                "The path provided is not a valid WordPress root directory.",
                self::INVALID_PATH
            );
        }

        if (!(is_string($dumpFilePath) && is_file($dumpFilePath) && is_readable($dumpFilePath))) {
            throw new InvalidArgumentException(
                "The dump file path provided is not a readable file.",
                self::DUMP_FILE_NOT_FOUND
            );
        }

        $db = (new Installation($path))->getDb();

        if ($db === null) {
            throw new RuntimeException("Could not get the database instance from the installation.");
        }

        $db->import($dumpFilePath);
        $output->writeln("Imported database dump from {$dumpFilePath}.");

        return 0;
    }
}
