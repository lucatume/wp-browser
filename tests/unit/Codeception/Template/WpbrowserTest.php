<?php namespace Codeception\Template;

use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use Symfony\Component\Process\Process;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class WpbrowserTest extends \Codeception\Test\Unit
{
    use TmpFilesCleanup;
    use SnapshotAssertions;

    /**
     * It should scaffold for plugin with plugin.php file
     *
     * @test
     */
    public function should_scaffold_for_plugin_with_plugin_php_file(): void
    {
        $composerFileCode = <<< EOT
{
  "name": "acme/plugin-89",
  "type": "wordpress-plugin",
  "require": {},
  "require-dev": {}
}
EOT;

        $projectDir = FS::tmpDir('project_factory_', [
            'plugin_89' => [
                'plugin.php' => "<?php\n/* Plugin Name: Plugin 89 */",
                'composer.json' => $composerFileCode
            ]
        ]);

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . Fs::relativePath(codecept_root_dir(), $projectDir . '/plugin_89'),
        ];
        $process = new Process($command);

        $process->setInput(
            "yes\n" // Yes, use recommended setup.
        );

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/plugin_89/tests/Support/_generated');

        $this->assertFileExists($projectDir . '/plugin_89/vendor/bin/chromedriver');
        $this->assertFileExists($projectDir . '/plugin_89/composer.lock');
        $this->assertFileExists($projectDir . '/plugin_89/tests/_wordpress/wp-config.php');
        $this->assertFileExists($projectDir . '/plugin_89/tests/Support/Data/dump.sql');

        // Remove generated or downloaded files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/plugin_89/tests/_wordpress');
        FS::rrmdir($projectDir . '/plugin_89/vendor');
        unlink($projectDir . '/plugin_89/composer.lock');
        unlink($projectDir . '/plugin_89/tests/Support/Data/dump.sql');

        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot($projectDir . '/plugin_89',
            fn() => $this->replaceRandomPorts(...func_get_args()));
    }

    /**
     * It should scaffold for plugin with non plugin.php file
     *
     * @test
     */
    public function should_scaffold_for_plugin_with_non_plugin_php_file(): void
    {
        $composerFileCode = <<< EOT
{
  "name": "acme/plugin-89",
  "type": "wordpress-plugin",
  "require": {},
  "require-dev": {}
}
EOT;

        $projectDir = FS::tmpDir('project_factory_', [
            'plugin_89' => [
                'main-file.php' => "<?php\n/* Plugin Name: Plugin 89 */",
                'composer.json' => $composerFileCode
            ]
        ]);

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . Fs::relativePath(codecept_root_dir(), $projectDir . '/plugin_89'),
        ];
        $process = new Process($command, null);

        $process->setInput(
            "yes\n" // Yes, use recommended setup.
        );

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/plugin_89/tests/Support/_generated');

        $this->assertFileExists($projectDir . '/plugin_89/vendor/bin/chromedriver');
        $this->assertFileExists($projectDir . '/plugin_89/composer.lock');
        $this->assertFileExists($projectDir . '/plugin_89/tests/_wordpress/wp-config.php');
        $this->assertFileExists($projectDir . '/plugin_89/tests/Support/Data/dump.sql');

        // Remove generated or downloaded files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/plugin_89/tests/_wordpress');
        FS::rrmdir($projectDir . '/plugin_89/vendor');
        FS::rrmdir($projectDir . '/plugin_89/var');
        unlink($projectDir . '/plugin_89/composer.lock');
        unlink($projectDir . '/plugin_89/tests/Support/Data/dump.sql');

        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot($projectDir . '/plugin_89',
            fn() => $this->replaceRandomPorts(...func_get_args()));
    }

    /**
     * It should scaffold for plugin with plugin php file custom
     *
     * @test
     */
    public function should_scaffold_for_plugin_with_plugin_php_file_custom(): void
    {
        $composerFileCode = <<< EOT
{
  "name": "acme/plugin-89",
  "type": "wordpress-plugin",
  "require": {},
  "require-dev": {}
}
EOT;

        $projectDir = FS::tmpDir('project_factory_', [
            'plugin_89' => [
                'plugin.php' => "<?php\n/* Plugin Name: Plugin 89 */",
                'composer.json' => $composerFileCode
            ]
        ]);

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . Fs::relativePath(codecept_root_dir(), $projectDir . '/plugin_89'),
        ];
        $process = new Process($command);

        $process->setInput(
            "no\n" // No, do not use recommended setup.
        );

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/plugin_89/tests/Support/_generated');

        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot($projectDir . '/plugin_89',
            fn() => $this->replaceRandomPorts(...func_get_args()));
    }

    /**
     * It should scaffold for plugin with non plugin.php file custom
     *
     * @test
     */
    public function should_scaffold_for_plugin_with_non_plugin_php_file_custom(): void
    {
        $composerFileCode = <<< EOT
{
  "name": "acme/plugin-89",
  "type": "wordpress-plugin",
  "require": {},
  "require-dev": {}
}
EOT;

        $projectDir = FS::tmpDir('project_factory_', [
            'plugin_89' => [
                'main.php' => "<?php\n/* Plugin Name: Plugin 89 */",
                'composer.json' => $composerFileCode
            ]
        ]);

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . Fs::relativePath(codecept_root_dir(), $projectDir . '/plugin_89'),
        ];
        $process = new Process($command);

        $process->setInput(
            "no\n" // No, do not use recommended setup.
        );

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/plugin_89/tests/Support/_generated');

        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot($projectDir . '/plugin_89',
            fn() => $this->replaceRandomPorts(...func_get_args()));
    }

    private function replaceRandomPorts(array $expected, array $actual, string $file): array
    {
        if (!str_ends_with($file, 'tests/.env')) {
            return [$expected, $actual];
        }

        $expected = explode("\n",
            preg_replace('/\\d{3,}$/um', '{port}', implode("\n", $expected))
        );

        $actual = explode("\n",
            preg_replace('/\\d{3,}$/um', '{port}', implode("\n", $actual))
        );

        return [$expected, $actual];
    }
}
