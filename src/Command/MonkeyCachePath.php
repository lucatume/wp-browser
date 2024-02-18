<?php

namespace lucatume\WPBrowser\Command;

use Codeception\CustomCommandInterface;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MonkeyCachePath extends Command implements CustomCommandInterface
{

    public static function getCommandName(): string
    {
        return 'monkey:cache:path';
    }

    public function getDescription(): string
    {
        return 'Returns the path to the monkey patch directory.';
    }

    public function configure(): void
    {
        $this->addOption(
            'porcelain',
            'p',
            InputOption::VALUE_NONE,
            'Output only the path to the monkey patch directory.'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = MonkeyPatch::getCachePath();

        if ($input->hasOption('porcelain')) {
            $output->writeln($path);
        } else {
            $output->writeln("<info>Monkey patch cache path</info>: {$path}");
        }

        return 0;
    }
}
