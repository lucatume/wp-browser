<?php

namespace lucatume\WPBrowser\Command;

use Codeception\Command\Run as CodeceptionRunCommand;
use Codeception\Command\Shared\ConfigTrait;
use Codeception\CustomCommandInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class RunAll extends Command implements CustomCommandInterface
{
    use ConfigTrait;

    public static function getCommandName(): string
    {
        return 'run:all';
    }

    public function getDescription(): string
    {
        return 'Runs all the test suites, each in a separate process.';
    }

    protected function configure(): void
    {
        $codeceptionRunCommandDefinition = (new CodeceptionRunCommand)->getDefinition();
        $this->setName(self::getCommandName())
            ->setDescription($this->getDescription())
            ->setDefinition($codeceptionRunCommandDefinition);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $argv;
        $codeceptBin = $argv[0];

        if (method_exists($input, '__toString')) {
            $runOptions = array_slice(explode(' ', $input->__toString()), 1);
        } else {
            $runOptions = [];
        }

        foreach ($this->getSuites() as $suite) {
            try {
                $process = new Process([$codeceptBin, 'run', $suite, ...$runOptions]);
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
