<?php
namespace Codeception\Template;

use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Tests\FSTemplates\BedrockProject;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Codeception;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationState\InstallationStateInterface;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

/**
 * @group slow
 */
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

        $expected = explode(
            "\n",
            preg_replace('/\\d{3,}$/um', '{port}', implode("\n", $expected))
        );


        $actual = explode(
            "\n",
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

        $projectDir = FS::tmpDir('setup_', [
            'plugin_89' => [
                'plugin.php' => "<?php\n/* Plugin Name: Plugin 89 */",
                'composer.json' => $composerFileCode,
                'vendor' => [
                    'bin' => [
                    ]
                ],
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
        $process = new Process($command, null, ['COMPOSER_BIN_DIR' => $projectDir . '/plugin_89/vendor/bin']);

        $process->setInput(
            "yes\n" // Yes, use recommended setup.
        );

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/plugin_89/' . Codeception::supportDir() . '/_generated');

        $this->assertFileExists($projectDir . '/plugin_89/vendor/bin/chromedriver');
        $this->assertFileExists($projectDir . '/plugin_89/tests/_wordpress/wp-config.php');
        $this->assertFileExists($projectDir . '/plugin_89/' . Codeception::dataDir() . '/dump.sql');

        // Remove generated or downloaded files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/plugin_89/tests/_wordpress');
        FS::rrmdir($projectDir . '/plugin_89/vendor');
        FS::rrmdir($projectDir . '/plugin_89/var');
        unlink($projectDir . '/plugin_89/' . Codeception::dataDir() . '/dump.sql');
        unlink($projectDir . '/plugin_89/composer');

        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot(
            $projectDir . '/plugin_89',
            fn() => $this->replaceRandomPorts(...func_get_args())
        );
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

        $projectDir = FS::tmpDir('setup_', [
            'plugin_89' => [
                'main-file.php' => "<?php\n/* Plugin Name: Plugin 89 */",
                'composer.json' => $composerFileCode,
                'vendor' => [
                    'bin' => [
                    ]
                ],
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
        $process = new Process($command, null, ['COMPOSER_BIN_DIR' => $projectDir . '/plugin_89/vendor/bin']);

        $process->setInput(
            "yes\n" // Yes, use recommended setup.
        );

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/plugin_89/' . Codeception::supportDir() . '/_generated');

        $this->assertFileExists($projectDir . '/plugin_89/vendor/bin/chromedriver');
        $this->assertFileExists($projectDir . '/plugin_89/tests/_wordpress/wp-config.php');
        $this->assertFileExists($projectDir . '/plugin_89/' . Codeception::dataDir() . '/dump.sql');

        // Remove generated or downloaded files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/plugin_89/tests/_wordpress');
        FS::rrmdir($projectDir . '/plugin_89/vendor');
        FS::rrmdir($projectDir . '/plugin_89/var');
        unlink($projectDir . '/plugin_89/' . Codeception::dataDir() . '/dump.sql');
        unlink($projectDir . '/plugin_89/composer');

        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot(
            $projectDir . '/plugin_89',
            fn() => $this->replaceRandomPorts(...func_get_args())
        );
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

        $projectDir = FS::tmpDir('setup_', [
            'plugin_89' => [
                'plugin.php' => "<?php\n/* Plugin Name: Plugin 89 */",
                'composer.json' => $composerFileCode,
                'vendor' => [
                    'bin' => [
                    ]
                ],
            ]
        ]);

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . $projectDir . '/plugin_89'
        ];
        $process = new Process($command, null, ['COMPOSER_BIN_DIR' => $projectDir . '/plugin_89/vendor/bin']);

        $process->setInput(
            "no\n" // No, do not use recommended setup.
        );

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/plugin_89/' . Codeception::supportDir() . '/_generated');
        FS::rrmdir($projectDir . '/plugin_89/tests/_wordpress');
        FS::rrmdir($projectDir . '/plugin_89/vendor');
        FS::rrmdir($projectDir . '/plugin_89/var');
        $dataDir = Codeception::dataDir($projectDir.'/plugin_89');
        unlink($dataDir. "/dump.sql");


        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot(
            $projectDir . '/plugin_89',
            fn() => $this->replaceRandomPorts(...func_get_args())
        );
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

        $projectDir = FS::tmpDir('setup_', [
            'plugin_89' => [
                'main.php' => "<?php\n/* Plugin Name: Plugin 89 */",
                'composer.json' => $composerFileCode,
                'vendor' => [
                    'bin' => [
                    ]
                ],
            ]
        ]);

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . $projectDir . '/plugin_89'
        ];
        $process = new Process($command, null, ['COMPOSER_BIN_DIR' => $projectDir . '/plugin_89/vendor/bin']);

        $process->setInput(
            "no\n" // No, do not use recommended setup.
        );

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/plugin_89/' . Codeception::supportDir() . '/_generated');
        FS::rrmdir($projectDir . '/plugin_89/tests/_wordpress');
        FS::rrmdir($projectDir . '/plugin_89/vendor');
        FS::rrmdir($projectDir . '/plugin_89/var');
        $dataDir = Codeception::dataDir($projectDir.'/plugin_89');
        unlink($dataDir. "/dump.sql");

        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot(
            $projectDir . '/plugin_89',
            fn() => $this->replaceRandomPorts(...func_get_args())
        );
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

        $projectDir = FS::tmpDir('setup_', [
            'theme_23' => [
                'style.css' => "/*\nTheme Name: Theme 23\n*/",
                'composer.json' => $composerFileCode,
                'vendor' => [
                    'bin' => [
                    ]
                ],
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
        $process = new Process($command, null, ['COMPOSER_BIN_DIR' => $projectDir . '/theme_23/vendor/bin']);

        $process->setInput(
            "yes\n" // Yes, use recommended setup.
        );

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/theme_23/' . Codeception::supportDir() . '/_generated');

        $this->assertFileExists($projectDir . '/theme_23/vendor/bin/chromedriver');
        $this->assertFileExists($projectDir . '/theme_23/tests/_wordpress/wp-config.php');
        $this->assertFileExists($projectDir . '/theme_23/' . Codeception::dataDir() . '/dump.sql');

        // Remove generated or downloaded files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/theme_23/tests/_wordpress');
        FS::rrmdir($projectDir . '/theme_23/vendor');
        FS::rrmdir($projectDir . '/theme_23/var');
        unlink($projectDir . '/theme_23/' . Codeception::dataDir() . '/dump.sql');
        unlink($projectDir . '/theme_23/composer');

        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot(
            $projectDir . '/theme_23',
            fn() => $this->replaceRandomPorts(...func_get_args())
        );
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

        $projectDir = FS::tmpDir('setup_', [
            'theme_23' => [
                'style.css' => <<< EOT
/*
Theme Name: Theme 23
Template: twentytwenty
*/
EOT,
                'composer.json' => $composerFileCode,
                'vendor' => [
                    'bin' => [
                    ]
                ],
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
        $process = new Process($command, null, ['COMPOSER_BIN_DIR' => $projectDir . '/theme_23/vendor/bin']);

        $process->setInput(
            "yes\n" // Yes, use recommended setup.
        );

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/theme_23/' . Codeception::supportDir() . '/_generated');

        $this->assertFileExists($projectDir . '/theme_23/vendor/bin/chromedriver');
        $this->assertFileExists($projectDir . '/theme_23/tests/_wordpress/wp-config.php');
        $this->assertFileExists($projectDir . '/theme_23/' . Codeception::dataDir() . '/dump.sql');

        // Remove generated or downloaded files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/theme_23/tests/_wordpress');
        FS::rrmdir($projectDir . '/theme_23/vendor');
        FS::rrmdir($projectDir . '/theme_23/var');
        unlink($projectDir . '/theme_23/' . Codeception::dataDir() . '/dump.sql');
        unlink($projectDir . '/theme_23/composer');

        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot(
            $projectDir . '/theme_23',
            fn() => $this->replaceRandomPorts(...func_get_args())
        );
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

        $projectDir = FS::tmpDir('setup_', [
            'theme_23' => [
                'style.css' => <<< EOT
/*
Theme Name: Theme 23
*/
EOT,
                'composer.json' => $composerFileCode,
                'vendor' => [
                    'bin' => [
                    ]
                ],
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
        $process = new Process($command, null, ['COMPOSER_BIN_DIR' => $projectDir . '/theme_23/vendor/bin']);

        $process->setInput(
            "no\n" // No, do not use recommended setup.
        );

        $process->mustRun();

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/theme_23/' . Codeception::supportDir() . '/_generated');
        FS::rrmdir($projectDir . '/theme_23/tests/_wordpress');
        FS::rrmdir($projectDir . '/theme_23/vendor');
        FS::rrmdir($projectDir . '/theme_23/var');
        $dataDir = Codeception::dataDir($projectDir.'/theme_23');
        unlink($dataDir. "/dump.sql");

        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot(
            $projectDir . '/theme_23',
            fn() => $this->replaceRandomPorts(...func_get_args())
        );
    }

    /**
     * It should scaffold for single site correctly
     *
     * @test
     */
    public function should_scaffold_for_single_site_correctly(): void
    {
        $composerFileCode = <<< EOT
{
  "name": "acme/site-project",
  "type": "project",
  "require": {},
  "require-dev": {}
}
EOT;

        $projectDir = FS::tmpDir('setup_', [
            'site' => [
                'composer.json' => $composerFileCode,
                'vendor' => [
                    'bin' => [
                    ]
                ],
            ]
        ]);
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($projectDir . '/site')
            ->configure($db)
            ->install(
                'https://the-project.local',
                'admin',
                'secret',
                'admin@the-project.local',
                'The Project',
            );

        $this->mockComposerBin($projectDir . '/site');

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . $projectDir . '/site'
        ];
        $process = new Process($command, null, ['COMPOSER_BIN_DIR' => $projectDir . '/site/vendor/bin']);

        $process->setInput(
            "yes\n" // Yes, use recommended setup.
        );

        $process->mustRun();

        $this->assertDirectoryExists($projectDir . '/site/wp-content/mu-plugins/sqlite-database-integration');
        $this->assertFileExists($projectDir . '/site/wp-content/db.php');
        $this->assertFileExists($projectDir . '/site/' . Codeception::dataDir() . '/dump.sql');
        $this->assertFileExists($projectDir . '/site/' . Codeception::dataDir() . '/db.sqlite');
        $this->assertFileExists($projectDir . '/site/tests/EndToEnd/ActivationCest.php');
        $this->assertFileExists($projectDir . '/site/tests/Integration/SampleTest.php');
        $this->assertFileExists($projectDir . '/site/codeception.yml');

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/site/' . Codeception::supportDir() . '/_generated');
        // Remove the binary sqlite file and the dump file.
        unlink($projectDir . '/site/' . Codeception::dataDir() . '/db.sqlite');
        unlink($projectDir . '/site/' . Codeception::dataDir() . '/dump.sql');

        $this->assertMatchesStringSnapshot(file_get_contents($projectDir . '/site/codeception.yml'));
        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot(
            $projectDir . '/site/tests',
            function (array $expected, array $actual, string $file) {
                return $this->replaceRandomPorts($expected, $actual, $file);
            }
        );
    }

    /**
     * It should scaffold for multi-site correctly
     *
     * @test
     */
    public function should_scaffold_for_multi_site_correctly(): void
    {
        $composerFileCode = <<< EOT
{
  "name": "acme/site-project",
  "type": "project",
  "require": {},
  "require-dev": {}
}
EOT;

        $projectDir = FS::tmpDir('setup_', [
            'site' => [
                'composer.json' => $composerFileCode,
                'vendor' => [
                    'bin' => [
                    ]
                ],
            ]
        ]);
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        Installation::scaffold($projectDir . '/site')
            ->configure($db, InstallationStateInterface::MULTISITE_SUBDOMAIN)
            ->install(
                'https://the-project.local',
                'admin',
                'secret',
                'admin@the-project.local',
                'The Project',
            );

        $this->mockComposerBin($projectDir . '/site');

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . $projectDir . '/site'
        ];
        $process = new Process($command, null, ['COMPOSER_BIN_DIR' => $projectDir . '/site/vendor/bin']);

        $process->setInput(
            "yes\n" // Yes, use recommended setup.
        );

        $process->mustRun();

        $this->assertDirectoryExists($projectDir . '/site/wp-content/mu-plugins/sqlite-database-integration');
        $this->assertFileExists($projectDir . '/site/wp-content/db.php');
        $this->assertFileExists($projectDir . '/site/' . Codeception::dataDir() . '/dump.sql');
        $this->assertFileExists($projectDir . '/site/' . Codeception::dataDir() . '/db.sqlite');
        $this->assertFileExists($projectDir . '/site/tests/EndToEnd/ActivationCest.php');
        $this->assertFileExists($projectDir . '/site/tests/Integration/SampleTest.php');
        $this->assertFileExists($projectDir . '/site/codeception.yml');

        // Remove the generated files that are not needed for the snapshot.
        FS::rrmdir($projectDir . '/site/' . Codeception::supportDir() . '/_generated');
        // Remove the binary sqlite file and the dump file.
        unlink($projectDir . '/site/' . Codeception::dataDir() . '/db.sqlite');
        unlink($projectDir . '/site/' . Codeception::dataDir() . '/dump.sql');

        $this->assertMatchesStringSnapshot(file_get_contents($projectDir . '/site/codeception.yml'));
        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot(
            $projectDir . '/site/tests',
            function (array $expected, array $actual, string $file) {
                return $this->replaceRandomPorts($expected, $actual, $file);
            }
        );
    }

    /**
     * It should scaffold correctly on site with non default structure
     *
     * @test
     */
    public function should_scaffold_correctly_on_site_with_non_default_structure(): void
    {
        if (PHP_VERSION < 8.0 || Codecept::VERSION < 5.0) {
            $this->markTestSkipped('This test requires PHP 8.0 or higher.');
        }

        $projectDir = FS::tmpDir('setup_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        (new BedrockProject($db, 'https://the-project.local'))->scaffold($projectDir);

        $this->mockComposerBin($projectDir);

        $command = [
            PHP_BINARY,
            codecept_root_dir("vendor/bin/codecept"),
            'init',
            'wpbrowser',
            '--path=' . $projectDir
        ];
        $process = new Process($command);

        $process->setInput(
            "no\n" // No, do not use recommended setup.
        );

        $process->mustRun();

        // Remove some hashed files.
        FS::rrmdir($projectDir . '/' . Codeception::supportDir() . '/_generated');

        // Random ports will change: visit the data to replace the random ports with a placeholder.
        $this->assertMatchesDirectorySnapshot(
            $projectDir . '/tests',
            fn() => $this->replaceRandomPorts(...func_get_args())
        );
    }
}
