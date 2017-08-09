<?php

namespace Step\Cli;

use Symfony\Component\Yaml\Yaml;

class CodeceptionCommand extends \CliTester {

    public function createSandbox() {
        mkdir($this->sandboxPath(), 0777, true);
        $this->runCodecept('bootstrap', $this->sandboxPath());
        $config = $this->sandboxPath('codeception.yml');
        $parsed = Yaml::parse(file_get_contents($config));
        if ( ! isset($parsed['extensions'])) {
            $parsed['extensions'] = [];
        }
        $parsed['extensions']['commands'] = [
            'Codeception\Command\DbSnapshot',
            'Codeception\Command\GenerateWPAjax',
            'Codeception\Command\GenerateWPCanonical',
            'Codeception\Command\GenerateWPRestApi',
            'Codeception\Command\GenerateWPRestController',
            'Codeception\Command\GenerateWPRestPostTypeController',
            'Codeception\Command\GenerateWPUnit',
            'Codeception\Command\GenerateWPXMLRPC',
            'tad\Codeception\Command\SearchReplace',
        ];
        file_put_contents($config, Yaml::dump($parsed));
    }

    public function sandboxPath($path = '') {
        $sandboxPath = codecept_output_dir('sandbox');

        return empty($path) ? $sandboxPath : $sandboxPath . DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR);
    }

    public function runCodecept($subCommand, $path = '') {
        $codecept = wpbrowser_vendor_path('bin/codecept');

        $command = trim("{$codecept} {$subCommand} {$path}");

        $this->runShellCommand($command, true);
    }

    public function runWpcept($subCommand, $path = '') {
        $wpcept = codecept_root_dir('wpcept');

        $command = trim("{$wpcept} {$subCommand} {$path}");

        $this->runShellCommand($command, true);
    }

    public function amInSandbox() {
        $this->amInPath($this->sandboxPath());
    }

    public function deleteSandbox() {
        rrmdir($this->sandboxPath());
    }
}
