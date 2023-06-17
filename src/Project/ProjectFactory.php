<?php

namespace lucatume\WPBrowser\Project;

use InvalidArgumentException;

class ProjectFactory
{
    public static function fromDir(string $workDir): ProjectInterface
    {
        // If we find a style.css file in the work directory we assume it's a theme.
        if (file_exists($workDir . '/style.css')) {
            return self::make('theme', $workDir);
        }

        if (PluginProject::findPluginFile($workDir)) {
            return self::make('plugin', $workDir);
        }

        // Assume it's a site.
        return self::make('site', $workDir);
    }

    public static function make(string $projectType, string $workDir): ProjectInterface
    {
        return match ($projectType) {
            'plugin' => new PluginProject($workDir),
            'theme' => new ThemeProject($workDir),
            'site' => new SiteProject(),
            default => throw new InvalidArgumentException("Unknown project type $projectType."),
        };
    }
}
