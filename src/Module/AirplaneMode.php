<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Module;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class AirplaneMode extends Module
{

    /**
     * @var string[]
     */
    protected $requiredFields = ['muPluginsDir'];

    /**
     * @var array{symlink: bool}
     */
    protected $config = [
        'symlink' => false
    ];

    protected function validateConfig(): void
    {
        parent::validateConfig();

        $config = $this->config;
        /** @var array{muPluginsDir: string, symlink: bool} $config */
        $config['symlink'] = (bool)$config['symlink'];

        if (!is_string($config['muPluginsDir'])) {
            throw new ModuleConfigException(
                __CLASS__,
                'The muPluginsDir configuration parameter must be a string.'
            );
        }

        $muPluginsDir = $config['muPluginsDir'];

        if (is_file($muPluginsDir) && !is_dir($muPluginsDir)) {
            throw new ModuleConfigException(
                __CLASS__,
                'The muPluginsDir configuration parameter must be a directory.'
            );
        }
    }

    public function _initialize(): void
    {
        /** @var array{muPluginsDir: string, symlink: bool} $config */
        $config = $this->config;
        $muPluginsDir = $config['muPluginsDir'];

        try {
            FS::mkdirp($muPluginsDir, [], 0755);
        } catch (\Exception $e) {
            throw new ModuleException(
                __CLASS__,
                'The muPluginsDir configuration parameter is not a directory and cannot be created.'
            );
        }

        if (!$this->config['symlink']) {
            $this->copyPlugin($muPluginsDir);
            return;
        }

        $this->symLinkPlugin($muPluginsDir);
    }

    public function _afterSuite(): void
    {
        /** @var array{muPluginsDir: string, symlink: bool} $config */
        $config = $this->config;
        $muPluginsDir = $config['muPluginsDir'];
        $pluginDir = $muPluginsDir . '/airplane-mode';

        if (!FS::rrmdir($pluginDir)) {
            throw new ModuleException(
                __CLASS__,
                'The airplane-mode plugin could not be removed from the mu-plugins directory.'
            );
        }

        if (is_file($muPluginsDir . '/airplane-mode-loader.php') && !unlink(
            $muPluginsDir . '/airplane-mode-loader.php'
        )) {
            throw new ModuleException(
                __CLASS__,
                'The airplane-mode loader could not be removed from the mu-plugins directory.'
            );
        }

        $this->debugSection('AirplaneMode', 'Airplane Mode plugin removed from mu-plugins directory.');
    }


    /**
     * @param mixed $muPluginsDir
     */
    private function copyPlugin($muPluginsDir): void
    {
        $pluginDir = dirname(__DIR__, 2) . '/includes/airplane-mode';
        $destination = $muPluginsDir . '/airplane-mode';

        if (!FS::recurseCopy($pluginDir, $destination)) {
            throw new ModuleException(
                __CLASS__,
                'The airplane-mode plugin could not be copied to the mu-plugins directory.'
            );
        }

        if (!rename($destination . '/airplane-mode-loader.php', $muPluginsDir . '/airplane-mode-loader.php')) {
            throw new ModuleException(
                __CLASS__,
                'The airplane-mode loader could not be moved to the mu-plugins directory.'
            );
        }

        $this->debugSection('AirplaneMode', 'Airplane Mode plugin copied to mu-plugins directory.');
    }

    /**
     * @param mixed $muPluginsDir
     */
    private function symLinkPlugin($muPluginsDir): void
    {
        $pluginDir = dirname(__DIR__, 2) . '/includes/airplane-mode';
        $destination = $muPluginsDir . '/airplane-mode';

        if (!symlink($pluginDir, $destination)) {
            throw new ModuleException(
                __CLASS__,
                'The airplane-mode plugin could not be symlinked to the mu-plugins directory.'
            );
        }

        if (!symlink($pluginDir . '/airplane-mode-loader.php', $muPluginsDir . '/airplane-mode-loader.php')) {
            throw new ModuleException(
                __CLASS__,
                'The airplane-mode loader could not be symlinked to the mu-plugins directory.'
            );
        }

        $this->debugSection('AirplaneMode', 'Airplane Mode plugin symlinked to mu-plugins directory.');
    }
}
