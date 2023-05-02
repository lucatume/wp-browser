<?php

namespace lucatume\WPBrowser\Project;

class PluginProject implements ProjectInterface
{
    private string $workDir;
    private string $pluginFile;

    public function __construct(string $workDir)
    {
        $this->workDir = $workDir;
        $this->pluginFile = $this->findPluginFile();
    }

    public function getType(): string
    {
        return 'plugin';
    }

    public function getPluginsString(): string
    {
        return basename($this->workDir) . '/' . basename($this->pluginFile);
    }

    private function findPluginFile(): string
    {
        $pluginFile = null;
        foreach (glob($this->workDir . '/*.php', false) as $file) {
            $content = file_get_contents($file);
            if (preg_match('/\s*Plugin Name:\s*(.*)\s*/', $content, $matches)) {
                $pluginFile = $file;
            }
        }

        if (empty($pluginFile)) {
            throw new \RuntimeException("Could not find plugin file.");
        }

        return realpath($pluginFile);
    }
}
