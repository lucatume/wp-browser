<?php

namespace lucatume\WPBrowser\Command;

use Codeception\Command\Run as CodeceptionRunCommand;
use Codeception\Command\Shared\ConfigTrait;
use Codeception\CustomCommandInterface;
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

    protected function configure()
    {
        $codeceptionRunCommandDefinition = (new CodeceptionRunCommand)->getDefinition();
        $this->setName(self::getCommandName())
            ->setDescription($this->getDescription())
            ->setDefinition($codeceptionRunCommandDefinition);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $_composer_bin_dir;
        $codeceptBin = DIRECTORY_SEPARATOR === '\\' ? 'codecept.bat' : 'codecept';
        $codeceptBinPathname = $_composer_bin_dir . DIRECTORY_SEPARATOR . $codeceptBin;
        $commandString = $input->__toString();
        $runOptions = array_slice(explode(' ', $commandString), 1);

        foreach ($this->getSuites() as $suite) {
            try {
                $process = new Process([$codeceptBinPathname, 'run', $suite, ...$runOptions]);
                $process->setTimeout(null);
                $process->start();

                foreach ($process as $data) {
                    $output->write($data);
                }

                if (!$process->isSuccessful()) {
                    return 1;
                }
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                return 1;
            }
        }
        return 0;
    }
}
