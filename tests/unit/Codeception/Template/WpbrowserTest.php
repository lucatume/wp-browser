<?php namespace Codeception\Template;

use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use Symfony\Component\Process\Process;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class WpbrowserTest extends \Codeception\Test\Unit
{
    use TmpFilesCleanup;
    use SnapshotAssertions;

    private function mockComposerBin(string $directory): void
    {
        $binCode = <<< EOT
#!/bin/sh
touch composer.lock
mkdir -p ./vendor/bin
touch ./vendor/bin/chromedriver
EOT;
        if (!file_put_contents($directory . '/composer', $binCode)) {
            throw new \RuntimeException("Could not create mock composer binary in $directory.");
        }

        if (!chmod($directory . '/composer', 0755)) {
            throw new \RuntimeException("Could not make mock composer binary in $directory executable.");
        }
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

        $this->mockComposerBin($projectDir . '/plugin_89');

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . $projectDir . '/plugin_89'
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
        unlink($projectDir . '/plugin_89/composer');

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

        $this->mockComposerBin($projectDir . '/plugin_89');

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . $projectDir . '/plugin_89'
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
        unlink($projectDir . '/plugin_89/composer');

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
            '--path=' . $projectDir . '/plugin_89'
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
            '--path=' . $projectDir . '/plugin_89'
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
     * It should scaffold for theme correctly
     *
     * @test
     */
    public function should_scaffold_for_theme_correctly(): void
    {
        $composerFileCode = <<< EOT
{
  "name": "acme/theme-23",
  "type": "wordpress-theme",
  "require": {},
  "require-dev": {}
}
EOT;

        $projectDir = FS::tmpDir('project_factory_', [
            'theme_23' => [
                'style.css' => "/*\nTheme Name: Theme 23\n*/",
                'composer.json' => $composerFileCode
            ]
        ]);

        $this->mockComposerBin($projectDir . '/theme_23');

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . $projectDir . '/theme_23'
        ];
        $process = new Process($command);

        $process->setInput(
            "yes\n" // Yes, use recommended setup.
        );

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/theme_23/tests/Support/_generated');

        $this->assertFileExists($projectDir . '/theme_23/vendor/bin/chromedriver');
        $this->assertFileExists($projectDir . '/theme_23/composer.lock');
        $this->assertFileExists($projectDir . '/theme_23/tests/_wordpress/wp-config.php');
        $this->assertFileExists($projectDir . '/theme_23/tests/Support/Data/dump.sql');

        // Remove generated or downloaded files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/theme_23/tests/_wordpress');
        FS::rrmdir($projectDir . '/theme_23/vendor');
        unlink($projectDir . '/theme_23/composer.lock');
        unlink($projectDir . '/theme_23/tests/Support/Data/dump.sql');
        unlink($projectDir . '/theme_23/composer');

        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot($projectDir . '/theme_23',
            fn() => $this->replaceRandomPorts(...func_get_args()));
    }

    /**
     * It should scaffold for child theme correctly
     *
     * @test
     */
    public function should_scaffold_for_child_theme_correctly(): void
    {
        $composerFileCode = <<< EOT
{
  "name": "acme/theme-23",
  "type": "wordpress-theme",
  "require": {},
  "require-dev": {}
}
EOT;

        $projectDir = FS::tmpDir('project_factory_', [
            'theme_23' => [
                'style.css' => <<< EOT
/*
Theme Name: Theme 23
Template: twentytwenty
*/
EOT ,
                'composer.json' => $composerFileCode
            ]
        ]);

        $this->mockComposerBin($projectDir . '/theme_23');

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . $projectDir . '/theme_23'
        ];
        $process = new Process($command);

        $process->setInput(
            "yes\n" // Yes, use recommended setup.
        );

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/theme_23/tests/Support/_generated');

        $this->assertFileExists($projectDir . '/theme_23/vendor/bin/chromedriver');
        $this->assertFileExists($projectDir . '/theme_23/composer.lock');
        $this->assertFileExists($projectDir . '/theme_23/tests/_wordpress/wp-config.php');
        $this->assertFileExists($projectDir . '/theme_23/tests/Support/Data/dump.sql');

        // Remove generated or downloaded files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/theme_23/tests/_wordpress');
        FS::rrmdir($projectDir . '/theme_23/vendor');
        unlink($projectDir . '/theme_23/composer.lock');
        unlink($projectDir . '/theme_23/tests/Support/Data/dump.sql');
        unlink($projectDir . '/theme_23/composer');

        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot($projectDir . '/theme_23',
            fn() => $this->replaceRandomPorts(...func_get_args()));
    }

    /**
     * It should scaffold for theme custom correctly
     *
     * @test
     */
    public function should_scaffold_for_theme_custom_correctly(): void
    {
        $composerFileCode = <<< EOT
{
  "name": "acme/theme-23",
  "type": "wordpress-theme",
  "require": {},
  "require-dev": {}
}
EOT;

        $projectDir = FS::tmpDir('project_factory_', [
            'theme_23' => [
                'style.css' => <<< EOT
/*
Theme Name: Theme 23
*/
EOT ,
                'composer.json' => $composerFileCode
            ]
        ]);

        $this->mockComposerBin($projectDir . '/theme_23');

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . $projectDir . '/theme_23'
        ];
        $process = new Process($command);

        $process->setInput(
            "no\n" // No, do not use recommended setup.
        );

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/theme_23/tests/Support/_generated');

        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot($projectDir . '/theme_23',
            fn() => $this->replaceRandomPorts(...func_get_args()));
    }
}
