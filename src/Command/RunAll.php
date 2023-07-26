<?php

namespace lucatume\WPBrowser\Command;

use Codeception\Command\Run;
use Codeception\CustomCommandInterface;
use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class RunAll extends Run implements CustomCommandInterface
{
    public static function getCommandName(): string
    {
        // Replace the Codeception `run` command with this one.
        return 'run';
    }

    public function getDescription(): string
    {
        return 'Runs all the test suites, each in a separate process.';
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getArgument('suite') || $input->getArgument('test')) {
            return parent::execute($input, $output);
        }

        global $argv;
        $codeceptBin = $argv[0];
        if (method_exists($input, '__toString')) {
            $runOptions = array_slice(explode(' ', $input->__toString()), 1);
        } else {
            $runOptions = [];
        }

        foreach ($this->getSuites() as $suite) {
            try {
                $cwd = getcwd();
                $process = new Process([$codeceptBin, 'codeception:run', $suite, ...$runOptions], $cwd);
                $process->setTimeout(null);
                $process->start();

                /** @var string $data */
                foreach ($process as $data) {
                    $output->write($data);
                }

                if (!$process->isSuccessful()) {
                    return 1;
                }
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
                return 1;
            }
        }
        return 0;
    }
}
