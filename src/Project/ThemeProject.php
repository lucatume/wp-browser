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
    private string $workDir;
    private string $themeName;
    private string $themeDir;

    public function __construct(string $workDir)
    {
        $this->workDir = $workDir;

        // Extract the theme name from the style.css file.
        $style = file_get_contents($workDir . '/style.css');
        preg_match('/\s*Theme Name:\s*(.*)\s*/', $style, $matches);
        if (empty($matches)) {
            throw new \RuntimeException("Could not find theme name in style.css file.");
        }
        $this->themeName = $matches[1];
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
