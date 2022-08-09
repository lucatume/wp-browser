<?php

namespace Step\Cli;

use lucatume\WPBrowser\Utils\Filesystem as FS;
use Symfony\Component\Yaml\Yaml;
use function lucatume\WPBrowser\vendorDir;

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
            'lucatume\WPBrowser\Command\GenerateWPAjax',
            'lucatume\WPBrowser\Command\GenerateWPCanonical',
            'lucatume\WPBrowser\Command\GenerateWPRestApi',
            'lucatume\WPBrowser\Command\GenerateWPRestController',
            'lucatume\WPBrowser\Command\GenerateWPRestPostTypeController',
            'lucatume\WPBrowser\Command\GenerateWPUnit',
            'lucatume\WPBrowser\Command\GenerateWPXMLRPC',
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
        $codecept = vendorDir('bin/codecept');

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
        FS::rrmdir($this->sandboxPath());
    }
}
