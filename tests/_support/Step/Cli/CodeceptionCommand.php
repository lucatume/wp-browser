<?php

namespace Step\Cli;

use CliTester;
use lucatume\WPBrowser\Utils\Composer;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use lucatume\WPBrowser\Command\GenerateWPAjax;
use lucatume\WPBrowser\Command\GenerateWPCanonical;
use lucatume\WPBrowser\Command\GenerateWPRestApi;
use lucatume\WPBrowser\Command\GenerateWPRestController;
use lucatume\WPBrowser\Command\GenerateWPRestPostTypeController;
use lucatume\WPBrowser\Command\GenerateWPUnit;
use lucatume\WPBrowser\Command\GenerateWPXMLRPC;

class CodeceptionCommand extends CliTester
{

    public function createSandbox(): void
    {
        if (!is_dir($this->sandboxPath()) &&
            !mkdir($concurrentDirectory = $this->sandboxPath(), 0777, true) &&
            !is_dir($concurrentDirectory)
        ) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        $this->runCodecept('bootstrap', $this->sandboxPath());
        $config = $this->sandboxPath('codeception.yml');
        $parsed = Yaml::parse(file_get_contents($config));
        if (! isset($parsed['extensions'])) {
            $parsed['extensions'] = [];
        }
        $parsed['extensions']['commands'] = [
            GenerateWPAjax::class,
            GenerateWPCanonical::class,
            GenerateWPRestApi::class,
            GenerateWPRestController::class,
            GenerateWPRestPostTypeController::class,
            GenerateWPUnit::class,
            GenerateWPXMLRPC::class,
        ];
        file_put_contents($config, Yaml::dump($parsed));
    }

    public function sandboxPath($path = ''): string
    {
        $sandboxPath = codecept_output_dir('sandbox');

        return empty($path) ? $sandboxPath : $sandboxPath . DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR);
    }

    public function runCodecept($subCommand, $path = ''): void
    {
        $codecept = Composer::vendorDir('bin/codecept');

        if (!empty($path)) {
            chdir($path);
        }

        $command = trim("{$codecept} {$subCommand}");

        $this->runShellCommand($command, true);
    }

    public function amInSandbox(): void
    {
        $this->amInPath($this->sandboxPath());
    }

    public function deleteSandbox(): void
    {
        FS::rrmdir($this->sandboxPath());
    }
}
