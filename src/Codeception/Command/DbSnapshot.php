<?php

namespace Codeception\Command;

use Codeception\CustomCommandInterface;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use tad\Codeception\Command\BaseCommand;
use tad\WPBrowser\Filesystem\Filesystem;
use tad\WPBrowser\Services\Db\MySQLDumpFactory;
use tad\WPBrowser\Services\Db\MySQLDumpFactoryInterface;

class DbSnapshot extends BaseCommand implements CustomCommandInterface {

    /**
     * @var MySQLDumpFactoryInterface
     */
    protected $pdoFactory;

    /**
     * @var \MySQLDump
     */
    protected $dump;
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        $name = null,
        MySQLDumpFactoryInterface $pdoFactory = null,
        Filesystem $filesystem = null
    ) {
        parent::__construct($name);
        $this->pdoFactory = $pdoFactory ? $pdoFactory : new MySQLDumpFactory();
        $this->filesystem = $filesystem ? $filesystem : new Filesystem();
    }

    /**
     * returns the name of the command
     *
     * @return string
     */
    public static function getCommandName() {
        return 'db:snapshot';
    }

    /**
     * Returns the `MySQLDump::tables` attribute.
     *
     * Used for internal check operations.
     *
     * @return array
     */
    public function _getDumpTables() {
        return empty($this->dump) ? [] : $this->dump->tables;
    }

    protected function configure() {
        $this->setName('db:snapshot')
             ->setDescription('Takes a snapshot of a database to be shared as a fixture.')
             ->addArgument('snapshot', InputArgument::REQUIRED,
                 'Specifies the filename (without extension) of the snapshot files.')
             ->addArgument('name', InputArgument::REQUIRED, 'Specifies the name of the database to snapshot.')
             ->addOption('host', null, InputOption::VALUE_OPTIONAL,
                 'If set the specified host will be used to connect to the database', 'localhost')
             ->addOption('user', 'u', InputOption::VALUE_OPTIONAL,
                 'If set the specified user will be used to connect to the database', 'root')
             ->addOption('pass', 'p', InputOption::VALUE_OPTIONAL,
                 'If set the specified password will be used to connect to the database', 'root')
             ->addOption('dump-file', null, InputOption::VALUE_OPTIONAL,
                 'If set the local version of the database will be dumped in the specified file; should be absolute path or a path relative to the root folder.')
             ->addOption('dist-dump-file', null, InputOption::VALUE_OPTIONAL,
                 'If set the distribution version of the database will be dumped in the specified file; should be absolute path or a path relative to the root folder.')
             ->addOption('skip-tables', null, InputOption::VALUE_OPTIONAL,
                 'A comma separated list of tables that should not be included in the dump.')
             ->addOption('local-url', null, InputOption::VALUE_OPTIONAL,
                 'The local setup domain that should be replaced in the distribution version of the dump file.',
                 'http://local.dev')
             ->addOption('dist-url', null, InputOption::VALUE_OPTIONAL,
                 'The distribution setup domain that should be used in the distribution version of the dump file.',
                 'http://dist.dev');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $host = $input->getOption('host');
        $user = $input->getOption('user');
        $pass = $input->getOption('pass');
        $dbName = $input->getArgument('name');

        try {
            $this->dump = $this->pdoFactory->makeDump($host, $user, $pass, $dbName);
        } catch (\PDOException $e) {
            throw new RuntimeException('Error while connecting to database [' . $dbName . ']: ' . $e->getMessage());
        }

        if (false === $this->dump) {
            $output->writeln('<error>Something went wrong with the dump component instance.</error>');

            return false;
        }

        \Codeception\Configuration::config();

        if ( ! empty($input->getOption('dump-file'))) {
            $dumpFile = $input->getOption('dump-file');
        } else {
            $dumpFile = codecept_data_dir($input->getArgument('snapshot') . '.sql');
        }

        $output->writeln('<info>Dump file will be written to [' . $dumpFile . ']</info>');

        if ( ! empty($input->getOption('dist-dump-file'))) {
            $distDumpFile = $input->getOption('dist-dump-file');
        } else {
            $distDumpFile = codecept_data_dir($input->getArgument('snapshot') . '.dist.sql');
        }

        $output->writeln('<info>Distribution version of dump file will be written to [' . $distDumpFile . ']</info>');

        $skipTables = $input->getOption('skip-tables');
        if ( ! empty($skipTables)) {
            $tables = explode(',', $skipTables);
            foreach ($tables as $table) {
                $this->dump->tables[$table] = \MySQLDump::NONE;
            }
        }

        $memory = fopen('php://memory', 'w');

        $this->dump->write($memory);

        rewind($memory);
        $dumpContents = stream_get_contents($memory);

        if ( ! $this->filesystem->file_put_contents($dumpFile, $dumpContents)) {
            $output->writeln('<error>Could not write dump to [' . $dumpFile . ']</error>');

            return false;
        }

        $output->writeln('<info>Dump file written to [' . $dumpFile . ']</info>');

        $localUrl = $input->getOption('local-url');
        $distUrl = $input->getOption('dist-url');
        $localDomain = rtrim(preg_replace('~http(s)*:\\/\\/(www\\.)*~', '', $localUrl), '/');
        $distDomain = rtrim(preg_replace('~http(s)*:\\/\\/(www\\.)*~', '', $distUrl), '/');
        $distDumpContents = str_replace($localDomain, $distDomain, $dumpContents);

        if ( ! $this->filesystem->file_put_contents($distDumpFile, $distDumpContents)) {
            $output->writeln('<error>Could not write dist dump to [' . $distDumpFile . ']</error>');

            return false;
        }

        $output->writeln('<info>Distribution version of dump file written to [' . $distDumpFile . ']</info>');
        $output->writeln('<comment>Any occurrence of [' . $localDomain . '] in it was replaced with [' . $distDomain . ']</comment>');

        parent::execute($input, $output);

        return true;
    }
}