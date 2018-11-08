<?php

namespace tad\WPBrowser\Extension;

use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use tad\WPBrowser\Filesystem\Utils;

class Copier extends Extension
{
    public static $events = [
        Events::MODULE_INIT => 'copyFiles',
        Events::SUITE_AFTER => 'removeFiles'
    ];

    public function __construct($config, $options)
    {
        if (!empty($config['files'])) {
            array_walk($config['files'], [$this, 'ensureSource']);
            array_walk($config['files'], [$this, 'ensureDestination']);
        }

        parent::__construct($config, $options);
    }

    public function copyFiles()
    {
        if (empty($this->config['files'])) {
            return;
        }

        array_walk($this->config['files'], [$this, 'copy']);
    }

    public function removeFiles()
    {
        if (empty($this->config['files'])) {
            return;
        }

        array_walk($this->config['files'], [$this, 'remove']);
    }

    protected function ensureSource($destination, $source)
    {
        if (!(
            (file_exists($source) || file_exists(getcwd() . DIRECTORY_SEPARATOR . trim($source, DIRECTORY_SEPARATOR)))
            && is_readable($source)
        )
        ) {
            throw new ExtensionException(__CLASS__, 'Source file [' . $source . '] does not exist');
        }
    }

    protected function ensureDestination($destination)
    {
        if (!(is_dir(dirname($destination)) && is_writable(dirname($destination)))) {
            throw new ExtensionException(__CLASS__, 'Destination [' . dirname($destination) . '] does not exist.');
        }
        if (file_exists($destination)) {
            $this->remove($destination);
        }
    }

    protected function remove($destination)
    {
        if (!file_exists($destination)) {
            return;
        }

        if (!is_dir($destination)) {
            unlink($destination);
            return;
        }
        Utils::recurseRemoveDir($destination);
    }

    protected function copy($destination, $source)
    {
        if (!is_dir($source)) {
            copy($source, $destination);
            return;
        }
        Utils::recurseCopy($source, $destination);
    }
}
