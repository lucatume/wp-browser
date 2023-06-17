<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package lucatume\WPBrowser\Project;
 */

namespace lucatume\WPBrowser\Project;

/**
 * Class ThemeProject.
 *
 * @since   TBD
 *
 * @package lucatume\WPBrowser\Project;
 */
class ThemeProject implements ProjectInterface
{
    private string $themeDir;

    public function __construct(string $workDir)
    {
        $this->themeDir = basename($workDir);
    }

    public function getType(): string
    {
        return 'theme';
    }

    public function getThemeString(): string
    {
        return $this->themeDir;
    }
}
