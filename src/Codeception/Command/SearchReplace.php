<?php

namespace Codeception\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SearchReplace extends Command
{
    protected function configure()
    {
        $this->setName('search-replace')
            ->setDescription('Search and replace a string in a database dump')
            ->addArgument('old', InputArgument::REQUIRED, 'A string to search for in the dump file')
            ->addArgument('new', InputArgument::REQUIRED, 'Replace instances of the `old` string with this new string')
            ->addArgument('file', InputArgument::REQUIRED, 'The path to the target SQL dump file')
            ->addOption('output', null, InputOption::VALUE_OPTIONAL, 'If set, the replaced contents will be written to this file')
            ->addOption('skip-if-missing', null, InputOption::VALUE_OPTIONAL, 'If set, the operation will not fail if source file is missing');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $skipIfMissing = $input->getOption('skip-if-missing');

        $sourceExists = file_exists($file);

        if ($skipIfMissing && !$sourceExists) {
            $output->writeln('<info>Skipped as source file [' . $file . '] is missing.</info>');
            return true;
        }

        if (!$sourceExists) {
            $output->writeln('<error>File [' . $file . '] does not exist.</error>');
            return false;
        }

        if (!is_readable($file)) {
            $output->writeln('<error>File [' . $file . '] is not readable.</error>');
            return false;
        }

        if (!is_writeable($file)) {
            $output->writeln('<error>File [' . $file . '] is not writeable.</error>');
            return false;
        }

        $contents = file_get_contents($input->getArgument('file'));

        $count = 0;
        $out = str_replace($input->getArgument('old'), $input->getArgument('new'), $contents, $count);

        $output->writeln('<info>Made ' . $count . ' replacements.</info>');

        $outputFile = $input->getOption('output');
        $outputFile = $outputFile ? $outputFile : $file;

        try {
            $exit = file_put_contents($outputFile, $out);
        } catch (\Exception $e) {
            $output->writeln('<error>Could not write to [' . $outputFile . '].</error>');
            return false;
        }

        if (empty($exit)) {
            $output->writeln('<error>Could not write to [' . $outputFile . '].</error>');
            return false;
        }

        $output->writeln('<info>Modified contents written to [' . $outputFile . '].</info>');
        return true;
    }
}