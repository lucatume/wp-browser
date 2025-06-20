<?php

namespace lucatume\WPBrowser\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Extension;
use lucatume\WPBrowser\WordPress\Installation;

class Symlinker extends Extension
{
    /**
     * @var array<string,array{0: string, 1: int}>
     */
    protected static $events = [
        Events::MODULE_INIT => ['onModuleInit', 0],
        Events::SUITE_AFTER => ['afterSuite', 0],
    ];

    /**
     * @var string
     */
    private $wpRootFolder = '';
    /**
     * @var string[]
     */
    private $plugins = [];
    /**
     * @var string[]
     */
    private $themes = [];
    /**
     * @var string
     */
    private $pluginsDir = '';
    /**
     * @var string
     */
    private $themesDir = '';
    /**
     * @var string[]
     */
    private $unlinkTargets = [];
    /**
     * @var bool
     */
    private $cleanupAfterSuite = false;

    /**
     * @throws ModuleConfigException
     */
    public function _initialize(): void
    {
        parent::_initialize();
        $wpRootFolder = $this->config['wpRootFolder'] ?? null;

        if (empty($wpRootFolder) || !is_string($wpRootFolder) || !is_dir($wpRootFolder)) {
            throw new ModuleConfigException($this, 'The `wpRootFolder` configuration parameter must be set.');
        }

        $plugins = $this->config['plugins'] ?? [];

        if (!is_array($plugins)) {
            throw new ModuleConfigException($this, 'The `plugins` configuration parameter must be an array.');
        }

        foreach ($plugins as $plugin) {
            $realpath = realpath($plugin);

            if (!$realpath) {
                throw new ModuleConfigException($this, "Plugin file $plugin does not exist.");
            }

            $this->plugins[] = $realpath;
        }

        $themes = $this->config['themes'] ?? [];

        if (!is_array($themes)) {
            throw new ModuleConfigException($this, 'The `themes` configuration parameter must be an array.');
        }

        foreach ($themes as $theme) {
            $realpath = realpath($theme);

            if (!$realpath) {
                throw new ModuleConfigException($this, "Theme directory $theme does not exist.");
            }

            $this->themes[] = $realpath;
        }

        $this->wpRootFolder = $wpRootFolder;

        $this->cleanupAfterSuite = isset($this->config['cleanupAfterSuite']) ?
            (bool)$this->config['cleanupAfterSuite']
            : false;
    }

    /**
     * @throws ModuleConfigException
     * @throws ModuleException
     */
    public function onModuleInit(SuiteEvent $event): void
    {
        try {
            $installation = new Installation($this->wpRootFolder);
            $this->pluginsDir = $installation->getPluginsDir();
            $this->themesDir = $installation->getThemesDir();
        } catch (\Throwable $e) {
            throw new ModuleConfigException(
                $this,
                'The `wpRootFolder` does not point to a valid WordPress installation.'
            );
        }

        foreach ($this->plugins as $plugin) {
            $this->symlinkPlugin($plugin, $this->pluginsDir);
        }

        foreach ($this->themes as $theme) {
            $this->symlinkTheme($theme, $this->themesDir);
        }
    }

    /**
     * @throws ModuleException
     */
    private function symlinkPlugin(string $plugin, string $pluginsDir): void
    {
        $link = rtrim($pluginsDir, "\\/") .DIRECTORY_SEPARATOR. ltrim(basename($plugin), "\\/");

        if (is_link($link)) {
            $target = readlink($link);

            if ($target && realpath($target) === $plugin) {
                // Already existing, but not managed by the extension.
                codecept_debug(
                    "[Symlinker] Found $link not managed by the extension: this will not be removed after the suite."
                );
                return;
            }

            throw new ModuleException(
                $this,
                "Could not symlink plugin $plugin to $link: link already exists and target is $target."
            );
        }

        if (!symlink($plugin, $link)) {
            throw new ModuleException($this, "Could not symlink plugin $plugin to $link.");
        }

        $this->unlinkTargets [] = $link;
        codecept_debug("[Symlinker] Symlinked plugin $plugin to $link.");
    }

    /**
     * @throws ModuleException
     */
    private function symlinkTheme(string $theme, string $themesDir): void
    {
        $target = $theme;
        $link = rtrim($themesDir, "\\/") . DIRECTORY_SEPARATOR .  ltrim(basename($theme), "\\/");

        if (is_link($link)) {
            $target = readlink($link);

            if ($target && realpath($target) === $theme) {
                codecept_debug(
                    "[Symlinker] Found $link not managed by the extension: this will not be removed after the suite."
                );
                return;
            }

            throw new ModuleException(
                $this,
                "Could not symlink theme $theme to $link: link already exists and target is $target."
            );
        }

        if (!symlink($target, $link)) {
            throw new ModuleException($this, "Could not symlink theme $theme to $link.");
        }

        $this->unlinkTargets [] = $link;
        codecept_debug("[Symlinker] Symlinked theme $theme to $link.");
    }

    /**
     * @throws ModuleException
     */
    public function afterSuite(SuiteEvent $event): void
    {
        if (!$this->cleanupAfterSuite) {
            return;
        }

        foreach ($this->unlinkTargets as $target) {
            if (!unlink($target)) {
                throw new ModuleException($this, "Could not unlink $target.");
            }
            codecept_debug("[Symlinker] Unlinked $target.");
        }
    }
}
