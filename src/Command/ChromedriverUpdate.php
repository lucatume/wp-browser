<?php

namespace lucatume\WPBrowser\Command;

use Codeception\CustomCommandInterface;
use JsonException;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Utils\ChromedriverInstaller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ChromedriverUpdate extends Command implements CustomCommandInterface
{
    public static function getCommandName(): string
    {
        return 'chromedriver:update';
    }

    public function configure(): void
    {
        $this->setDescription('Updates the Chromedriver binary.')
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'The version of Chrome to install chromedriver for.',
                ''
            )->addOption(
                'platform',
                '',
                InputOption::VALUE_REQUIRED,
                'The platform to install Chromedriver for.',
                ''
            )->addOption(
                'binary',
                '',
                InputOption::VALUE_REQUIRED,
                'The path to the Chrome binary to download Chromedriver for.',
                false
            );
    }

    /**
     * @throws JsonException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $platform = $input->getOption('platform') ?: null;

        if ($platform && !is_string($platform)) {
            throw new InvalidArgumentException('The platform option must be a string.');
        }

        $version = $input->getArgument('version') ?: null;

        if ($version && !is_string($version)) {
            throw new InvalidArgumentException('The version argument must be a string.');
        }

        $binary = $input->getOption('binary') ?: null;

        if ($binary && !is_string($binary)) {
            throw new InvalidArgumentException('The binary option must be a string.');
        }

        $chromedriverInstaller = new ChromedriverInstaller($version, $platform, $binary, $output);
        $chromedriverInstaller->install();

        return 0;
    }
}
