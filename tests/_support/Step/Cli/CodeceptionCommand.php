<?php

namespace Step\Cli;

use Symfony\Component\Yaml\Yaml;

class CodeceptionCommand extends \CliTester
{

    public function createSandbox()
    {
        if (!is_dir($this->sandboxPath()) &&
            !mkdir($concurrentDirectory = $this->sandboxPath(), 0777, true) &&
            !is_dir($concurrentDirectory)
        ) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        $this->runCodecept('bootstrap', $this->sandboxPath());
        $config = $this->sandboxPath('codeception.yml');
        $parsed = Yaml::parse(file_get_contents($config));
        if (! isset($parsed['extensions'])) {
            $parsed['extensions'] = [];
        }
        $parsed['extensions']['commands'] = [
            'Codeception\Command\GenerateWPAjax',
            'Codeception\Command\GenerateWPCanonical',
            'Codeception\Command\GenerateWPRestApi',
            'Codeception\Command\GenerateWPRestController',
            'Codeception\Command\GenerateWPRestPostTypeController',
            'Codeception\Command\GenerateWPUnit',
            'Codeception\Command\GenerateWPXMLRPC',
        ];
        file_put_contents($config, Yaml::dump($parsed));
    }

    public function sandboxPath($path = '')
    {
        $sandboxPath = codecept_output_dir('sandbox');

        return empty($path) ? $sandboxPath : $sandboxPath . DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR);
    }

    public function runCodecept($subCommand, $path = '')
    {
        $codecept = wpbrowser_vendor_path('bin/codecept');

        if (!empty($path)) {
            chdir($path);
        }

        $command = trim("{$codecept} {$subCommand}");

        $this->runShellCommand($command, true);
    }

    public function amInSandbox()
    {
        $this->amInPath($this->sandboxPath());
    }

    public function deleteSandbox()
    {
        rrmdir($this->sandboxPath());
    }
}
