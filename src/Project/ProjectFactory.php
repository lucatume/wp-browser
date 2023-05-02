<?php

namespace lucatume\WPBrowser\Project;

class ProjectFactory
{
    public static function fromDir(string $workDir): ProjectInterface
    {
        // If we find a style.css file in the work directory we assume it's a theme.
        if (file_exists($workDir . '/style.css')) {
            return self::make('theme', $workDir);
        }

        // Iterate on each PHP file in the work directory and check if it contains a plugin header.
        foreach (glob($workDir . '/*.php', false) as $file) {
            $content = file_get_contents($file);
            if (preg_match('/\s*Plugin Name:\s*(.*)\s*/', $content, $matches)) {
                return self::make('plugin', $workDir);
            }
        }

        // Assume it's a site.
        return self::make('site', $workDir);
    }

    public static function make(string $projectType, string $workDir): ProjectInterface
    {
        return match ($projectType) {
            'plugin' => new PluginProject($workDir),
            'theme' => new ThemeProject($workDir),
            'site' => new SiteProject($workDir),
            default => throw new \InvalidArgumentException("Unknown project type $projectType."),
        };
    }
}
