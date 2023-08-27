<?php

namespace unit\lucatume\WPBrowser\Project;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Project\ThemeProject;
use lucatume\WPBrowser\Tests\Traits\CliCommandTestingTools;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Tests\Traits\UopzFunctions;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class ThemeProjectTest extends Unit
{
    use TmpFilesCleanup;
    use UopzFunctions;
    use CliCommandTestingTools;
    use SnapshotAssertions;

    /**
     * It should throw if directory does not exist
     *
     * @test
     */
    public function should_throw_if_directory_does_not_exist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(ThemeProject::ERR_INVALID_THEME_DIR);

        new ThemeProject(new ArrayInput([]), new NullOutput(), __DIR__ . '/not-a-dir');
    }

    /**
     * It should throw if style.css does not exist
     *
     * @test
     */
    public function should_throw_if_style_css_does_not_exist(): void
    {
        $projectDir = FS::tmpDir('theme_project_', []);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(ThemeProject::ERR_INVALID_THEME_DIR);

        new ThemeProject(new ArrayInput([]), new NullOutput(), $projectDir);
    }

    /**
     * It should throw if style.css does not define Theme Name
     *
     * @test
     */
    public function should_throw_if_style_css_does_not_define_theme_name(): void
    {
        $projectDir = FS::tmpDir('theme_project_', [
            'style.css' => <<<CSS
/*
 Text Domain: some-domain
 */
CSS
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(ThemeProject::ERR_INVALID_THEME_DIR);

        new ThemeProject(new ArrayInput([]), new NullOutput(), $projectDir);
    }

    /**
     * It should build correctly on theme directory
     *
     * @test
     */
    public function should_build_correctly_on_theme_directory(): void
    {
        $projectDir = FS::tmpDir('theme_project_', [
            'style.css' => <<<CSS
/*
Theme Name: Some Theme
 */
CSS
        ]);

        $themeProject = new ThemeProject(new ArrayInput([]), new NullOutput(), $projectDir);

        $this->assertEquals(basename($projectDir), $themeProject->getActivationString());
        $this->assertEquals('Some Theme', $themeProject->getName());
        $this->assertEquals('theme', $themeProject->getType());
    }

    /**
     * It should build correctly on child theme directory
     *
     * @test
     */
    public function should_build_correctly_on_child_theme_directory(): void
    {
        $projectDir = FS::tmpDir('theme_project_', [
            'style.css' => <<<CSS
/*
Theme Name: Some Theme
Template: some-parent-theme
 */
CSS
        ]);

        $themeProject = new ThemeProject(new ArrayInput([]), new NullOutput(), $projectDir);

        $this->assertEquals(basename($projectDir), $themeProject->getActivationString());
        $this->assertEquals('Some Theme', $themeProject->getName());
        $this->assertEquals('theme', $themeProject->getType());
    }
}
