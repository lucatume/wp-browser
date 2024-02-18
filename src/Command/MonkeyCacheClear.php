<?php

namespace lucatume\WPBrowser\Command;

use Codeception\CustomCommandInterface;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MonkeyCacheClear extends Command implements CustomCommandInterface
{

    public static function getCommandName(): string
    {
        return 'monkey:cache:clear';
    }

    public function getDescription(): string
    {
        return 'Clears the monkey patch cache.';
    }

    public function configure(): void
    {
        $this->addOption(
            'porcelain',
            'p',
            InputOption::VALUE_NONE,
            'Suppress output.'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheDir = dirname(MonkeyPatch::getReplacementFileName(__FILE__, 'default'));

        if (!is_dir($cacheDir)) {
            return 0;
        }

        try {
            FS::rrmdir($cacheDir) && FS::mkdirp($cacheDir);
        } catch (\Throwable $t) {
            $output->writeln("<error>{$t->getMessage()}</error>");
            return 1;
        }

        if (!$input->hasOption('porcelain')) {
            $output->writeln("<info>Monkey patch cache cleared.</info>");
        }

        return 0;
    }
}
