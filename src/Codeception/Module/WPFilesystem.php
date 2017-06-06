<?php


namespace Codeception\Module;


use Codeception\Exception\ModuleConfigException;

class WPFilesystem extends Filesystem {

    protected $requiredFields = ['wpRootFolder'];

    public function _initialize() {
        $this->ensureWpRootFolder();
    }

    protected function ensureWpRootFolder() {
        $message =
            "[{$this->config['wpRootFolder']}] is not a valid WordPress root folder.\n\nThe WordPress root folder is the one that contains the 'wp-load.php' file.";

        if ( ! (is_dir($this->config['wpRootFolder']) && is_readable($this->config['wpRootFolder']) && is_writable($this->config['wpRootFolder']))) {
            throw new ModuleConfigException(__CLASS__, $message);
        }

        if ( ! file_exists($this->config['wpRootFolder'] . '/wp-load.php')) {
            throw new ModuleConfigException(__CLASS__, $message);
        }
    }
}