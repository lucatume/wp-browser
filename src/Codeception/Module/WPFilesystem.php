<?php


namespace Codeception\Module;


use Codeception\Exception\ModuleConfigException;
use tad\WPBrowser\Filesystem\Utils;

class WPFilesystem extends Filesystem {

    protected $requiredFields = ['wpRootFolder'];

    public function _initialize() {
        $this->ensureWpRootFolder();
        $this->ensureAndSetOptionalPaths();
    }

    protected function ensureWpRootFolder() {
        $wpRoot = $this->config['wpRootFolder'];

        if ( ! is_dir($wpRoot)) {
            $wpRoot = codecept_root_dir(Utils::unleadslashit($wpRoot));
        }

        $message = "[{$wpRoot}] is not a valid WordPress root folder.\n\nThe WordPress root folder is the one that contains the 'wp-load.php' file.";

        if ( ! (is_dir($wpRoot) && is_readable($wpRoot) && is_writable($wpRoot))) {
            throw new ModuleConfigException(__CLASS__, $message);
        }

        if ( ! file_exists($wpRoot . '/wp-load.php')) {
            throw new ModuleConfigException(__CLASS__, $message);
        }

        $this->config['wpRootFolder'] = Utils::untrailslashit($wpRoot) . DIRECTORY_SEPARATOR;
    }

    protected function ensureAndSetOptionalPaths() {
        $optionalPaths = [
            'themes'     => ['mustExist' => true, 'default' => '/wp-content/themes'],
            'plugins'    => ['mustExist' => true, 'default' => '/wp-content/plugins'],
            'mu-plugins' => ['mustExist' => false, 'default' => '/wp-content/mu-plugins'],
            'uploads'    => ['mustExist' => true, 'default' => '/wp-content/uploads'],
        ];
        $wpRoot = $this->config['wpRootFolder'];
        foreach ($optionalPaths as $configKey => $info) {
            if (empty($this->config[$configKey])) {
                $path = $info['default'];
            } else {
                $path = $this->config[$configKey];
            }
            if ( ! is_dir($path)) {
                $path = Utils::unleadslashit($path);
                $absolutePath = $wpRoot . $path;
            } else {
                $absolutePath = $path;
            }
            $mustExistAndIsNotDir = $info['mustExist'] && ! is_dir($absolutePath);
            $canNotExistButIsDefined = ! empty($this->config[$configKey]) && ! is_dir($absolutePath);
            if ($mustExistAndIsNotDir || $canNotExistButIsDefined) {
                throw new ModuleConfigException(__CLASS__, "The {$configKey} config path [{$path}] does not exist.");
            }
            $this->config[$configKey] = Utils::untrailslashit($absolutePath) . DIRECTORY_SEPARATOR;
        }
    }
}