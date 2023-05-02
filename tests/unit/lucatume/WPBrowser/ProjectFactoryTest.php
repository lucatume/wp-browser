<?php


namespace Unit\lucatume\WPBrowser;

use lucatume\WPBrowser\Project\PluginProject;
use lucatume\WPBrowser\Project\ProjectFactory;
use lucatume\WPBrowser\Project\SiteProject;
use lucatume\WPBrowser\Project\ThemeProject;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class ProjectFactoryTest extends \Codeception\Test\Unit
{
    /**
     * It should make plugin project when a plugin file is found
     *
     * @test
     */
    public function should_make_plugin_project_when_a_plugin_file_is_found(): void
    {
        $pluginDir = FS::tmpDir('project_factory_', [
            'plugin.php' => "<?php\n/* Plugin Name: Test */"
        ]);

        $project = ProjectFactory::fromDir($pluginDir);
        $this->assertInstanceOf(PluginProject::class, $project);
        $this->assertEquals('plugin', $project->getType());
        $this->assertEquals(basename($pluginDir) . '/plugin.php', $project->getPluginsString());
    }

    /**
     * It should make plugin project when plugin file with arbitrary name is found
     *
     * @test
     */
    public function should_make_plugin_project_when_plugin_file_with_arbitrary_name_is_found(): void
    {
        $pluginDir = FS::tmpDir('project_factory_', [
            'foo.php' => "<?php\n/* Plugin Name: Test */"
        ]);

        $project = ProjectFactory::fromDir($pluginDir);
        $this->assertInstanceOf(PluginProject::class, $project);
        $this->assertEquals('plugin', $project->getType());
        $this->assertEquals(basename($pluginDir) . '/foo.php', $project->getPluginsString());
    }

    /**
     * It should make theme project when a style.css file is found
     *
     * @test
     */
    public function should_make_theme_project_when_a_style_css_file_is_found(): void
    {
        $themeDir = FS::tmpDir('project_factory_', [
            'style.css' => "/* Theme Name: Test */"
        ]);

        $project = ProjectFactory::fromDir($themeDir);
        $this->assertInstanceOf(ThemeProject::class, $project);
        $this->assertEquals('theme', $project->getType());
        $this->assertEquals(basename($themeDir), $project->getThemeString());
    }

    /**
     * It should make site project when not theme or plugin
     *
     * @test
     */
    public function should_make_site_project_when_not_theme_or_plugin(): void
    {
        $themeDir = FS::tmpDir('project_factory_');

        $project = ProjectFactory::fromDir($themeDir);
        $this->assertInstanceOf(SiteProject::class, $project);
        $this->assertEquals('site', $project->getType());
    }

    /**
     * It should make different projects correctly
     *
     * @test
     */
    public function should_make_different_projects_correctly(): void
    {
        $dir = FS::tmpDir('project_factory_', [
            'style.css' => "/* Theme Name: Test */",
            'plugin.php' => "<?php\n/* Plugin Name: Test */"
        ]);

        $this->assertInstanceOf(ThemeProject::class, ProjectFactory::make('theme', $dir));
        $this->assertInstanceOf(PluginProject::class, ProjectFactory::make('plugin', $dir));
        $this->assertInstanceOf(SiteProject::class, ProjectFactory::make('site', $dir));
    }
}
