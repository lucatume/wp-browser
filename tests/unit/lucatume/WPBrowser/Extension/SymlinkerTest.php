<?php

namespace unit\lucatume\WPBrowser\Extension;

use Codeception\Codecept;
use Codeception\Event\SuiteEvent;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Suite;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Extension\Symlinker;
use lucatume\WPBrowser\Tests\Traits\LoopIsolation;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\Installation;
use PHPUnit\Framework\Assert;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SymlinkerTest extends Unit
{
    use LoopIsolation;
    use TmpFilesCleanup;

    private function getSuiteEvent(): SuiteEvent
    {
        if (Codecept::VERSION >= 5) {
            return new SuiteEvent(new Suite(new EventDispatcher(), 'test'));
        }

        // Codeception 4.x.
        return new SuiteEvent(new Suite());
    }

    public function test_exists(): void
    {
        $symlinker = new Symlinker([
            'wpRootFolder' => __DIR__,
        ], []);

        $this->assertInstanceOf(Symlinker::class, $symlinker);
    }

    public function test_throw_if_wp_root_folder_is_not_set(): void
    {
        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessage('The `wpRootFolder` configuration parameter must be set.');
        $suiteEvent = $this->getSuiteEvent();

        $symlinker = new Symlinker([
        ], []);
        $symlinker->onModuleInit($suiteEvent);
    }

    public function test_throw_if_wp_root_folder_does_not_point_to_a_valid_installation(): void
    {
        $symlinker = new Symlinker([
            'wpRootFolder' => __DIR__,
        ], []);
        $suiteEvent = $this->getSuiteEvent();

        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessage('The `wpRootFolder` does not point to a valid WordPress installation.');

        $this->assertInIsolation(static function () use ($symlinker, $suiteEvent) {
            $symlinker->onModuleInit($suiteEvent);
        });
    }

    public function test_throw_if_plugins_are_not_array(): void
    {
        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessage('The `plugins` configuration parameter must be an array.');
        $suiteEvent = $this->getSuiteEvent();

        $symlinker = new Symlinker([
            'wpRootFolder' => __DIR__,
            'plugins' => 'not-an-array',
        ], []);
        $symlinker->onModuleInit($suiteEvent);
    }

    public function test_throw_if_themes_are_not_array(): void
    {
        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessage('The `themes` configuration parameter must be an array.');
        $suiteEvent = $this->getSuiteEvent();

        $symlinker = new Symlinker([
            'wpRootFolder' => __DIR__,
            'themes' => 'not-an-array',
        ], []);
        $symlinker->onModuleInit($suiteEvent);
    }

    public function test_without_plugins_or_themes(): void
    {
        $workingDir = FS::tmpDir('symlinker_');
        $wpRoot = FS::tmpDir('symlinker_');
        Installation::scaffold($wpRoot);
        $suiteEvent = $this->getSuiteEvent();

        $symlinker = new Symlinker([
            'wpRootFolder' => $wpRoot,
        ], []);

        $this->assertInIsolation(static function () use ($symlinker, $workingDir, $suiteEvent) {
            chdir($workingDir);

            Assert::assertSame($workingDir, getcwd());

            $symlinker->onModuleInit($suiteEvent);
            $symlinker->afterSuite($suiteEvent);
        });
    }

    public function test_throws_if_plugin_file_does_not_exist(): void
    {
        $wpRoot = FS::tmpDir('symlinker_', [
            'wp-content' => [
                'plugins' => [],
                'themes' => []
            ]
        ]);

        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessage('Plugin file not-a-file/plugin.php does not exist.');

        $symlinker = new Symlinker([
            'wpRootFolder' => $wpRoot,
            'plugins' => [
                'not-a-file/plugin.php',
            ],
        ], []);
    }

    public function test_throws_if_theme_is_not_a_directory(): void
    {
        $wpRoot = FS::tmpDir('symlinker_', [
            'wp-content' => [
                'plugins' => [],
                'themes' => []
            ]
        ]);

        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessage('Theme directory not-a-directory does not exist.');

        $symlinker = new Symlinker([
            'wpRootFolder' => $wpRoot,
            'themes' => [
                'not-a-directory',
            ],
        ], []);
    }

    public function test_with_relative_paths(): void
    {
        $workingDir = FS::tmpDir('symlinker_', [
            'vendor' => [
                'acme' => [
                    'plugin-1' => [
                        'plugin-1.php' => <<< PHP
                        <?php
                        /** Plugin Name: Plugin 1 */
                        function plugin_1_canary() {}

                        register_activation_hook( __FILE__, 'activate_plugin_1' );
                        function activate_plugin_1(){
                            update_option('plugin_1_activated', 1);
                    }
                    PHP
                    ],
                    'plugin-2' => [
                        'main.php' => <<< PHP
                        <?php
                        /** Plugin Name: Plugin 2 */
                        function plugin_2_canary() {}

                        register_activation_hook( __FILE__, 'activate_plugin_2' );
                        function activate_plugin_2(){
                            update_option('plugin_2_activated', 1);
                    }
                    PHP
                    ],
                    'theme-1' => [
                        'style.css' => <<< CSS
                        /*
                        Theme Name: Theme 1
                        */
                        CSS,
                        'index.php' => '<?php // This file is required for the theme to work. ?>',
                        'functions.php' => <<< PHP
                        <?php
                        function theme_1_some_function() {
                            return 'test-test-test';
                        }

                        add_action('after_setup_theme','theme_1_some_function');
                        PHP
                    ],
                    'theme-2' => [
                        'style.css' => <<< CSS
                        /*
                        Theme Name: Theme 2
                        */
                        CSS,
                        'index.php' => '<?php // This file is required for the theme to work. ?>',
                        'functions.php' => <<< PHP
                        <?php
                        function theme_2_some_function() {
                            return 'test-test-test';
                        }

                        add_action('after_setup_theme','theme_2_some_function');
                        PHP
                    ]
                ]
            ]
        ]);
        $wpRoot = FS::tmpDir('symlinker_');
        Installation::scaffold($wpRoot);
        $suiteEvent = $this->getSuiteEvent();

        $this->assertInIsolation(static function () use ($workingDir, $wpRoot, $suiteEvent) {
            chdir($workingDir);

            Assert::assertSame($workingDir, getcwd());

            $symlinker = new Symlinker([
                'wpRootFolder' => $wpRoot,
                'cleanupAfterSuite' => true,
                'plugins' => [
                    'vendor/acme/plugin-1',
                    'vendor/acme/plugin-2'
                ],
                'themes' => [
                    'vendor/acme/theme-1',
                    'vendor/acme/theme-2'
                ]
            ], []);

            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/functions.php');

            $symlinker->onModuleInit($suiteEvent);

            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/functions.php');

            $symlinker->afterSuite($suiteEvent);

            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/functions.php');
        });
    }

    public function test_with_absolute_paths(): void
    {
        $workingDir = FS::tmpDir('symlinker_', [
            'vendor' => [
                'acme' => [
                    'plugin-1' => [
                        'plugin-1.php' => <<< PHP
                        <?php
                        /** Plugin Name: Plugin 1 */
                        function plugin_1_canary() {}

                        register_activation_hook( __FILE__, 'activate_plugin_1' );
                        function activate_plugin_1(){
                            update_option('plugin_1_activated', 1);
                    }
                    PHP
                    ],
                    'plugin-2' => [
                        'main.php' => <<< PHP
                        <?php
                        /** Plugin Name: Plugin 2 */
                        function plugin_2_canary() {}

                        register_activation_hook( __FILE__, 'activate_plugin_2' );
                        function activate_plugin_2(){
                            update_option('plugin_2_activated', 1);
                    }
                    PHP
                    ],
                    'theme-1' => [
                        'style.css' => <<< CSS
                        /*
                        Theme Name: Theme 1
                        */
                        CSS,
                        'index.php' => '<?php // This file is required for the theme to work. ?>',
                        'functions.php' => <<< PHP
                        <?php
                        function theme_1_some_function() {
                            return 'test-test-test';
                        }

                        add_action('after_setup_theme','theme_1_some_function');
                        PHP
                    ],
                    'theme-2' => [
                        'style.css' => <<< CSS
                        /*
                        Theme Name: Theme 2
                        */
                        CSS,
                        'index.php' => '<?php // This file is required for the theme to work. ?>',
                        'functions.php' => <<< PHP
                        <?php
                        function theme_2_some_function() {
                            return 'test-test-test';
                        }

                        add_action('after_setup_theme','theme_2_some_function');
                        PHP
                    ]
                ]
            ]
        ]);
        $wpRoot = FS::tmpDir('symlinker_');
        Installation::scaffold($wpRoot);
        $suiteEvent = $this->getSuiteEvent();

        $this->assertInIsolation(static function () use ($workingDir, $wpRoot, $suiteEvent) {
            chdir($workingDir);

            Assert::assertSame($workingDir, getcwd());

            $symlinker = new Symlinker([
                'wpRootFolder' => $wpRoot,
                'cleanupAfterSuite' => true,
                'plugins' => [
                    $workingDir . '/vendor/acme/plugin-1',
                    $workingDir . '/vendor/acme/plugin-2'
                ],
                'themes' => [
                    $workingDir . '/vendor/acme/theme-1',
                    $workingDir . '/vendor/acme/theme-2'
                ]
            ], []);

            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/functions.php');

            $symlinker->onModuleInit($suiteEvent);

            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/functions.php');

            $symlinker->afterSuite($suiteEvent);

            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/functions.php');
        });
    }

    public function test_will_not_cleanup_after_suite_by_default(): void
    {
        $workingDir = FS::tmpDir('symlinker_', [
            'vendor' => [
                'acme' => [
                    'plugin-1' => [
                        'plugin-1.php' => <<< PHP
                        <?php
                        /** Plugin Name: Plugin 1 */
                        function plugin_1_canary() {}

                        register_activation_hook( __FILE__, 'activate_plugin_1' );
                        function activate_plugin_1(){
                            update_option('plugin_1_activated', 1);
                    }
                    PHP
                    ],
                    'plugin-2' => [
                        'main.php' => <<< PHP
                        <?php
                        /** Plugin Name: Plugin 2 */
                        function plugin_2_canary() {}

                        register_activation_hook( __FILE__, 'activate_plugin_2' );
                        function activate_plugin_2(){
                            update_option('plugin_2_activated', 1);
                    }
                    PHP
                    ],
                    'theme-1' => [
                        'style.css' => <<< CSS
                        /*
                        Theme Name: Theme 1
                        */
                        CSS,
                        'index.php' => '<?php // This file is required for the theme to work. ?>',
                        'functions.php' => <<< PHP
                        <?php
                        function theme_1_some_function() {
                            return 'test-test-test';
                        }

                        add_action('after_setup_theme','theme_1_some_function');
                        PHP
                    ],
                    'theme-2' => [
                        'style.css' => <<< CSS
                        /*
                        Theme Name: Theme 2
                        */
                        CSS,
                        'index.php' => '<?php // This file is required for the theme to work. ?>',
                        'functions.php' => <<< PHP
                        <?php
                        function theme_2_some_function() {
                            return 'test-test-test';
                        }

                        add_action('after_setup_theme','theme_2_some_function');
                        PHP
                    ]
                ]
            ]
        ]);
        $wpRoot = FS::tmpDir('symlinker_');
        Installation::scaffold($wpRoot);
        $suiteEvent = $this->getSuiteEvent();

        $this->assertInIsolation(static function () use ($workingDir, $wpRoot, $suiteEvent) {
            chdir($workingDir);

            Assert::assertSame($workingDir, getcwd());

            $symlinker = new Symlinker([
                'wpRootFolder' => $wpRoot,
                'plugins' => [
                    $workingDir . '/vendor/acme/plugin-1',
                    $workingDir . '/vendor/acme/plugin-2'
                ],
                'themes' => [
                    $workingDir . '/vendor/acme/theme-1',
                    $workingDir . '/vendor/acme/theme-2'
                ]
            ], []);

            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/functions.php');

            $symlinker->onModuleInit($suiteEvent);

            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/functions.php');

            $symlinker->afterSuite($suiteEvent);

            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/functions.php');
        });
    }

    public function test_will_not_cleanup_after_suite_if_configured_not_to(): void
    {
        $workingDir = FS::tmpDir('symlinker_', [
            'vendor' => [
                'acme' => [
                    'plugin-1' => [
                        'plugin-1.php' => <<< PHP
                        <?php
                        /** Plugin Name: Plugin 1 */
                        function plugin_1_canary() {}

                        register_activation_hook( __FILE__, 'activate_plugin_1' );
                        function activate_plugin_1(){
                            update_option('plugin_1_activated', 1);
                    }
                    PHP
                    ],
                    'plugin-2' => [
                        'main.php' => <<< PHP
                        <?php
                        /** Plugin Name: Plugin 2 */
                        function plugin_2_canary() {}

                        register_activation_hook( __FILE__, 'activate_plugin_2' );
                        function activate_plugin_2(){
                            update_option('plugin_2_activated', 1);
                    }
                    PHP
                    ],
                    'theme-1' => [
                        'style.css' => <<< CSS
                        /*
                        Theme Name: Theme 1
                        */
                        CSS,
                        'index.php' => '<?php // This file is required for the theme to work. ?>',
                        'functions.php' => <<< PHP
                        <?php
                        function theme_1_some_function() {
                            return 'test-test-test';
                        }

                        add_action('after_setup_theme','theme_1_some_function');
                        PHP
                    ],
                    'theme-2' => [
                        'style.css' => <<< CSS
                        /*
                        Theme Name: Theme 2
                        */
                        CSS,
                        'index.php' => '<?php // This file is required for the theme to work. ?>',
                        'functions.php' => <<< PHP
                        <?php
                        function theme_2_some_function() {
                            return 'test-test-test';
                        }

                        add_action('after_setup_theme','theme_2_some_function');
                        PHP
                    ]
                ]
            ]
        ]);
        $wpRoot = FS::tmpDir('symlinker_');
        Installation::scaffold($wpRoot);
        $suiteEvent = $this->getSuiteEvent();

        $this->assertInIsolation(static function () use ($workingDir, $wpRoot, $suiteEvent) {
            chdir($workingDir);

            Assert::assertSame($workingDir, getcwd());

            $symlinker = new Symlinker([
                'wpRootFolder' => $wpRoot,
                'cleanupAfterSuite' => false,
                'plugins' => [
                    $workingDir . '/vendor/acme/plugin-1',
                    $workingDir . '/vendor/acme/plugin-2'
                ],
                'themes' => [
                    $workingDir . '/vendor/acme/theme-1',
                    $workingDir . '/vendor/acme/theme-2'
                ]
            ], []);

            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileDoesNotExist($wpRoot . '/wp-content/themes/theme-2/functions.php');

            $symlinker->onModuleInit($suiteEvent);

            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/functions.php');

            $symlinker->afterSuite($suiteEvent);

            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/functions.php');
        });
    }

    public function test_will_leave_existing_symlinks_in_place(): void
    {
        $workingDir = FS::tmpDir('symlinker_', [
            'vendor' => [
                'acme' => [
                    'plugin-1' => [
                        'plugin-1.php' => <<< PHP
                        <?php
                        /** Plugin Name: Plugin 1 */
                        function plugin_1_canary() {}

                        register_activation_hook( __FILE__, 'activate_plugin_1' );
                        function activate_plugin_1(){
                            update_option('plugin_1_activated', 1);
                    }
                    PHP
                    ],
                    'plugin-2' => [
                        'main.php' => <<< PHP
                        <?php
                        /** Plugin Name: Plugin 2 */
                        function plugin_2_canary() {}

                        register_activation_hook( __FILE__, 'activate_plugin_2' );
                        function activate_plugin_2(){
                            update_option('plugin_2_activated', 1);
                    }
                    PHP
                    ],
                    'theme-1' => [
                        'style.css' => <<< CSS
                        /*
                        Theme Name: Theme 1
                        */
                        CSS,
                        'index.php' => '<?php // This file is required for the theme to work. ?>',
                        'functions.php' => <<< PHP
                        <?php
                        function theme_1_some_function() {
                            return 'test-test-test';
                        }

                        add_action('after_setup_theme','theme_1_some_function');
                        PHP
                    ],
                    'theme-2' => [
                        'style.css' => <<< CSS
                        /*
                        Theme Name: Theme 2
                        */
                        CSS,
                        'index.php' => '<?php // This file is required for the theme to work. ?>',
                        'functions.php' => <<< PHP
                        <?php
                        function theme_2_some_function() {
                            return 'test-test-test';
                        }

                        add_action('after_setup_theme','theme_2_some_function');
                        PHP
                    ]
                ]
            ]
        ]);
        $wpRoot = FS::tmpDir('symlinker_');
        Installation::scaffold($wpRoot);
        $suiteEvent = $this->getSuiteEvent();

        $this->assertInIsolation(static function () use ($workingDir, $wpRoot, $suiteEvent) {
            chdir($workingDir);

            Assert::assertSame($workingDir, getcwd());

            $symlinker = new Symlinker([
                'wpRootFolder' => $wpRoot,
                'cleanupAfterSuite' => true,
                'plugins' => [
                    $workingDir . '/vendor/acme/plugin-1',
                    $workingDir . '/vendor/acme/plugin-2'
                ],
                'themes' => [
                    $workingDir . '/vendor/acme/theme-1',
                    $workingDir . '/vendor/acme/theme-2'
                ]
            ], []);

            if (!(
                symlink($workingDir . '/vendor/acme/plugin-1', $wpRoot . '/wp-content/plugins/plugin-1')
                && symlink($workingDir . '/vendor/acme/plugin-2', $wpRoot . '/wp-content/plugins/plugin-2')
                && symlink($workingDir . '/vendor/acme/theme-1', $wpRoot . '/wp-content/themes/theme-1')
                && symlink($workingDir . '/vendor/acme/theme-2', $wpRoot . '/wp-content/themes/theme-2'))
            ) {
                throw new \RuntimeException('Could not create symlinks in ' . $wpRoot);
            }

            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/functions.php');

            $symlinker->onModuleInit($suiteEvent);

            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/functions.php');

            $symlinker->afterSuite($suiteEvent);

            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-1/plugin-1.php');
            Assert::assertFileExists($wpRoot . '/wp-content/plugins/plugin-2/main.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-1/functions.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/style.css');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/index.php');
            Assert::assertFileExists($wpRoot . '/wp-content/themes/theme-2/functions.php');
        });
    }

    public function test_will_throw_if_link_found_not_pointing_to_same_target(): void
    {
        $workingDir = FS::tmpDir('symlinker_', [
            'vendor' => [
                'acme' => [
                    'plugin-1' => [
                        'plugin-1.php' => <<< PHP
                        <?php
                        /** Plugin Name: Plugin 1 */
                        function plugin_1_canary() {}

                        register_activation_hook( __FILE__, 'activate_plugin_1' );
                        function activate_plugin_1(){
                            update_option('plugin_1_activated', 1);
                    }
                    PHP
                    ],
                    'plugin-2' => [
                        'main.php' => <<< PHP
                        <?php
                        /** Plugin Name: Plugin 2 */
                        function plugin_2_canary() {}

                        register_activation_hook( __FILE__, 'activate_plugin_2' );
                        function activate_plugin_2(){
                            update_option('plugin_2_activated', 1);
                    }
                    PHP
                    ]
                ]
            ]
        ]);
        $wpRoot = FS::tmpDir('symlinker_');
        $otherDir = FS::tmpDir('symlinker_');
        Installation::scaffold($wpRoot);
        $suiteEvent = $this->getSuiteEvent();

        $this->expectException(ModuleException::class);
        $this->expectExceptionMessage(
            "Could not symlink plugin $workingDir/vendor/acme/plugin-2 to $wpRoot/wp-content/plugins/plugin-2: link already exists and target is $otherDir."
        );

        $this->assertInIsolation(static function () use ($workingDir, $wpRoot, $otherDir, $suiteEvent) {
            chdir($workingDir);

            Assert::assertSame($workingDir, getcwd());

            $symlinker = new Symlinker([
                'wpRootFolder' => $wpRoot,
                'cleanupAfterSuite' => true,
                'plugins' => [
                    $workingDir . '/vendor/acme/plugin-1',
                    $workingDir . '/vendor/acme/plugin-2'
                ],
            ], []);

            if (!(
                symlink($workingDir . '/vendor/acme/plugin-1', $wpRoot . '/wp-content/plugins/plugin-1')
                && symlink($otherDir, $wpRoot . '/wp-content/plugins/plugin-2')
            )) {
                throw new \RuntimeException('Could not create symlinks in ' . $wpRoot);
            }

            $symlinker->onModuleInit($suiteEvent);
        });
    }

    public function test_allows_the_dot_as_relative_path(): void
    {
        $workingDir = FS::tmpDir('symlinker_', [
            'plugin.php' => <<< PHP
            <?php
            /** Plugin Name: Plugin 1 */
            function plugin_1_canary() {}
            PHP
        ]);
        $wpRoot = FS::tmpDir('symlinker_');
        Installation::scaffold($wpRoot);
        $suiteEvent = $this->getSuiteEvent();

        $this->assertInIsolation(static function () use ($workingDir, $wpRoot, $suiteEvent) {
            chdir($workingDir);

            Assert::assertSame($workingDir, getcwd());

            $symlinker = new Symlinker([
                'wpRootFolder' => $wpRoot,
                'plugins' => [
                    '.',
                ]
            ], []);

            $workDirBasename = basename($workingDir);

            Assert::assertFileDoesNotExist($wpRoot . "/wp-content/plugins/{$workDirBasename}/plugin.php");

            $symlinker->onModuleInit($suiteEvent);

            Assert::assertFileExists($wpRoot . "/wp-content/plugins/{$workDirBasename}/plugin.php");
            Assert::assertTrue(is_link($wpRoot . "/wp-content/plugins/{$workDirBasename}"));
            Assert::assertEquals($workingDir, readlink($wpRoot . "/wp-content/plugins/{$workDirBasename}"));
        });
    }
}
