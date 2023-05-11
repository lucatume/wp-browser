<?php

namespace lucatume\WPBrowser\Project;

use lucatume\WPBrowser\Exceptions\RuntimeException;

class PluginProject implements ProjectInterface
{
    private string $pluginFile;

    public function __construct(private string $workDir)
    {
        $pluginFile = self::findPluginFile($workDir);

        if ($pluginFile === false) {
            throw new RuntimeException("Could not find a plugin file in $workDir.");
        }

        $this->pluginFile = $pluginFile;
    }

    public function getType(): string
    {
        return 'plugin';
    }

    public function getPluginsString(): string
    {
        return basename($this->workDir) . '/' . basename($this->pluginFile);
    }

    public static function findPluginFile(string $workDir): string|false
    {
        $pluginFile = null;

        $glob = glob($workDir . '/*.php', GLOB_NOSORT);

        if ($glob === false) {
            return false;
        }

        foreach ($glob as $file) {
            $content = file_get_contents($file);

            if ($content === false) {
                continue;
            }

            if (preg_match('/\s*Plugin Name:\s*(.*)\s*/', $content)) {
                $pluginFile = $file;
            }
        }

        if (empty($pluginFile) || !($realpath = realpath($pluginFile))) {
            return false;
        }

        return $realpath;
    }
}
