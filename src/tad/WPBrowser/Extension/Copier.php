<?php

namespace tad\WPBrowser\Extension;


use Codeception\Event\SuiteEvent;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use tad\WPBrowser\Filesystem\Utils;

class Copier extends Extension
{
    public static $events = [
        'suite.before' => 'copyFiles',
        'suite.after' => 'removeFiles'
    ];

    public function __construct($config, $options)
    {
        if (!empty($config['files'])) {
            array_walk($config['files'], [$this, 'ensureSource']);
            array_walk($config['files'], [$this, 'ensureDestination']);
        }

        parent::__construct($config, $options);
    }

    public function copyFiles(SuiteEvent $event)
    {
        if (empty($this->config['files'])) {
            return;
        }

        array_walk($this->config['files'], [$this, 'copy']);
    }

    public function removeFiles(SuiteEvent $event)
    {
        if (empty($this->config['slug'])) {
            return;
        }

        if (!is_dir($this->themeDestinationFolder . $slug)) {
            return;
        }

        \tad\WPBrowser\Tests\Support\rrmdir($this->themeDestinationFolder . $slug);

        if (is_dir($this->themeDestinationFolder . $slug)) {
            throw new RuntimeException('Dummy theme [' . $this->themeDestinationFolder . $slug . '] could not be removed ');
        }
    }

    protected function ensureSource($destination, $source)
    {
        if (!(file_exists($source) && is_readable($source))) {
            throw new ExtensionException(__CLASS__, 'Source file [' . $source . '] does not exist');
        }
    }

    private function ensureDestination($destination)
    {
        if (!(is_dir(dirname($destination)) && is_writable(dirname($destination)))) {
            throw new ExtensionException(__CLASS__, 'Destination [' . dirname($destination) . '] does not exist.');
        }
        if (file_exists($destination)) {
            throw new ExtensionException(__CLASS__, 'Destination file [' . $destination . '] already exists');
        }
    }

    private function copy($destination, $source)
    {
        if (!is_dir($source)) {
            copy($source, $destination);
            return;
        }
        Utils::recurseCopy($source, $destination);
    }
}