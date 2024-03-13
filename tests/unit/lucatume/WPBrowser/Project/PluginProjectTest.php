<?php


namespace lucatume\WPBrowser\Project;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
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
class PluginProjectTest extends Unit
{
    use TmpFilesCleanup;
    use UopzFunctions;
    use CliCommandTestingTools;
    use SnapshotAssertions;

    /**
     * It should throw if built on non existing directory
     *
     * @test
     */
    public function should_throw_if_built_on_non_existing_directory(): void
    {
        $input = new ArrayInput([]);
        $output = new NullOutput();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(PluginProject::ERR_PLUGIN_NOT_FOUND);

        new PluginProject($input, $output, __DIR__ . '/not-a-dir');
    }

    /**
     * It should throw if directory found but not a plugin
     *
     * @test
     */
    public function should_throw_if_directory_found_but_not_a_plugin(): void
    {
        $pluginDir = FS::tmpDir('plugin_project_', []);
        $input = new ArrayInput([]);
        $output = new NullOutput();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(PluginProject::ERR_PLUGIN_NOT_FOUND);

        new PluginProject($input, $output, $pluginDir);
    }

    /**
     * It should build on plugin directory correctly
     *
     * @test
     */
    public function should_build_on_plugin_directory_correctly(): void
    {
        $pluginDir = FS::tmpDir('plugin_project_', [
            'plugin.php' => '<?php /* Plugin Name: Acme Plugin */',
        ]);
        $input = new ArrayInput([]);
        $output = new NullOutput();

        $pluginProject = new PluginProject($input, $output, $pluginDir);

        $this->assertEquals('Acme Plugin', $pluginProject->getName());
        $this->assertEquals($pluginDir . '/plugin.php', $pluginProject->getPluginFilePathName());
    }

    /**
     * It should provide information about the failure to activate due to error
     *
     * @test
     */
    public function should_provide_information_about_the_failure_to_activate_due_to_error(): void
    {
        $wpRootDir = FS::tmpDir('plugin_project_');
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
        FS::mkdirp($wpRootDir . '/wp-content/plugins/acme-plugin', [
            'plugin.php' => <<< PHP
<?php
/* Plugin Name: Acme Plugin */
throw new \Exception('Something went wrong.');
PHP

        ]);
        $pluginDir = $wpRootDir . '/wp-content/plugins/acme-plugin';
        $input = new ArrayInput([]);
        $output = new BufferedOutput();

        $pluginProject = new PluginProject($input, $output, $pluginDir);
        $this->assertFalse($pluginProject->activate($wpRootDir, 1234));
        $expected = "Could not activate plugin: Something went wrong. \n" .
            "{{wp_root_dir}}/wp-content/plugins/acme-plugin/plugin.php:3\n" .
            "This might happen because the plugin has unmet dependencies; wp-browser configuration will continue, " .
            "but you will need to manually activate the plugin and update the dump in tests/Support/Data/dump.sql.";
        $this->assertEquals(
            $expected,
            trim(str_replace($wpRootDir, '{{wp_root_dir}}', $output->fetch()))
        );
    }
}
