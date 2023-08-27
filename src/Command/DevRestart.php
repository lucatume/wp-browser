<?php

namespace lucatume\WPBrowser\Command;

use Codeception\CustomCommandInterface;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ExtensionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DevRestart extends Command implements CustomCommandInterface
{

    public static function getCommandName(): string
    {
        return 'dev:restart';
    }

    public function getDescription(): string
    {
        return 'Stops and restarts the testing environment services.';
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $application = $this->getApplication();

        if ($application === null) {
            $output->writeln('<error>Could not get the application instance.</error>');
            return 1;
        }

        if ($application->find('dev:stop')->execute($input, $output) !== 0) {
            return 1;
        }

        if ($application->find('dev:start')->execute($input, $output) !== 0) {
            return 1;
        }

        return 0;
    }
}
