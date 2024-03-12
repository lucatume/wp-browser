<?php

namespace unit\lucatume\WPBrowser\Project;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Project\ThemeProject;
use lucatume\WPBrowser\Tests\Traits\CliCommandTestingTools;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

/**
 * @group slow
 */
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

    /**
     * It should provide information about the failure to activate due to error
     *
     * @test
     */
    public function should_provide_information_about_the_failure_to_activate_due_to_error(): void
    {
        $wpRootDir = FS::tmpDir('theme_project_');
        $dbName = Random::dbName();
        $db = new MysqlDatabase(
            $dbName,
            Env::get('WORDPRESS_DB_USER'),
            Env::get('WORDPRESS_DB_PASSWORD'),
            Env::get('WORDPRESS_DB_HOST')
        );
        Installation::scaffold($wpRootDir)
            ->configure($db)
            ->install(
                'http://localhost:1234',
                'admin',
                'password',
                'admin@example.com',
                'Test'
            );
        FS::mkdirp($wpRootDir . '/wp-content/themes/acme-theme', [
            'style.css' => <<< PHP
<?php
/*
 Theme Name: Acme Theme
 Requires PHP: 23.89
 */
PHP,
            'index.php' => '<?php',
            'functions.php' => '<?php'
        ]);
        $themeDir = $wpRootDir . '/wp-content/themes/acme-theme';
        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $themeProject = new ThemeProject($input, $output, $themeDir);
        $this->assertFalse($themeProject->activate($wpRootDir, 1234));
        $expected = "Could not activate theme: Error: Current PHP version does not meet minimum requirements for Acme Theme. \n" .
            "This might happen because the theme has unmet dependencies; wp-browser configuration will continue, " .
            "but you will need to manually activate the theme and update the dump in tests/Support/Data/dump.sql.";
        $this->assertEquals(
            $expected,
            trim(str_replace($wpRootDir, '{{wp_root_dir}}', $output->fetch()))
        );
    }
}
