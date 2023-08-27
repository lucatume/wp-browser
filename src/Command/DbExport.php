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

class DbExport extends Command implements CustomCommandInterface
{

    public const INVALID_PATH = 1;
    public const DUMP_DIR_NOT_FOUND = 2;
    public const INSTALLATION_DB_NOT_FOUND = 3;

    public static function getCommandName(): string
    {
        return 'wp:db:export';
    }

    public function getDescription(): string
    {
        return 'Exports the database used by a suite to a file.';
    }

    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::REQUIRED, 'The path to the WordPress root directory.');
        $this->addArgument('dumpFilePath', InputArgument::REQUIRED, 'The path of the dump file to create.');
    }

    /**
     * @throws Throwable
     * @throws DbException
     * @throws WorkerException
     * @throws WpConfigFileException
     * @throws ProcessException
     * @throws InstallationException
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

        if (!(is_string($dumpFilePath) && is_dir(dirname($dumpFilePath)))) {
            throw new InvalidArgumentException(
                "The dump file path provided is not valid: the directory does not exist.",
                self::DUMP_DIR_NOT_FOUND
            );
        }

        $db = (new Installation($path))->getDb();

        if ($db === null) {
            throw new RuntimeException(
                "Could not get the database instance from the installation.",
                self::INSTALLATION_DB_NOT_FOUND
            );
        }

        $db->dump($dumpFilePath);
        $output->writeln("Database exported to {$dumpFilePath}.");

        return 0;
    }
}
