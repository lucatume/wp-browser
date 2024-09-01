<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use lucatume\WPBrowser\Module\WPLoader;
use lucatume\WPBrowser\Tests\Traits\DatabaseAssertions;
use lucatume\WPBrowser\Tests\Traits\LoopIsolation;
use lucatume\WPBrowser\Tests\Traits\MainInstallationAccess;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use PHPUnit\Framework\Assert;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

// @group slow
// @group isolated-2
class WPLoaderArbitraryThemeLocationTest extends Unit
{
    use SnapshotAssertions;
    use DatabaseAssertions;
    use LoopIsolation;
    use TmpFilesCleanup;
    use MainInstallationAccess;

    /**
     * @var \Codeception\Lib\ModuleContainer
     */
    private $mockModuleContainer;
    /**
     * @var mixed[]
     */
    private $config = [];

    /**
     * @after
     */
    public function undefineConstants(): void
    {
        foreach (
            [
                'DB_HOST',
                'DB_NAME',
                'DB_USER',
                'DB_PASSWORD',
            ] as $const
        ) {
            if (defined($const)) {
                uopz_undefine($const);
            }
        }
    }

    /**
     * It should allow loading theme from arbitrary location
     *
     * @test
     */
    public function should_allow_loading_theme_from_arbitrary_location(): void
    {
        $themeProjectDir = FS::tmpDir('wploader_', [
            'style.css' => <<<CSS
/*
Theme Name: My Theme
Author: My Author
Description: My Description
Version: 1.0.0
Tested up to: 5.9
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
CSS
,
            'functions.php' => <<<PHP
<?php
function my_theme_setup() {
    update_option('my_theme_setup_ran', '1');
}

add_action('after_setup_theme','my_theme_setup' );
PHP
,
            'index.php' => '<?php // This file is required for the theme to work. ?>',
            'var' => [
                'wordpress' => []
            ],
            'vendor' => [
                'acme' => [
                    'some-theme' => [
                        'style.css' => <<<CSS
/*
Theme Name: Acme Some Theme
Author: My Author
Description: My Description
Version: 1.0.0
Tested up to: 5.9
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
CSS
,
                        'functions.php' => <<<PHP
<?php
function acme_some_theme_setup() {
    update_option('acme_some_theme_setup_ran', '1');
}

add_action('after_setup_theme','acme_some_theme_setup' );
PHP
,
                        'index.php' => '<?php // This file is required for the theme to work. ?>',
                    ]
                ]
            ]
        ]);
        $wpRootDir = $themeProjectDir . '/var/wordpress';
        Installation::scaffold($wpRootDir, '6.1.1');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'tablePrefix' => 'test_',
            'theme' => $themeProjectDir,
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $themeProjectDir) {
                chdir($themeProjectDir);
                $themeName = basename($themeProjectDir);

                $wpLoader->_initialize();

                Assert::assertEquals($themeName, get_option('stylesheet'));
                Assert::assertEquals($themeName, get_option('template'));
                Assert::assertTrue(function_exists('my_theme_setup'));
                Assert::assertEquals('1', get_option('my_theme_setup_ran'));
                global $wp_stylesheet_path, $wp_template_path;
                Assert::assertEquals($themeProjectDir, $wp_stylesheet_path);
                Assert::assertEquals($themeProjectDir, $wp_template_path);
                $theme = wp_get_theme();
                Assert::assertInstanceOf(\WP_Theme::class, $theme);
                Assert::assertEquals('My Theme', $theme->get('Name'));
                Assert::assertEmpty($theme->errors());
                Assert::assertTrue($theme->exists());
            }
        );

        // Reconfigure the module to use a relative path.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'tablePrefix' => 'test_',
            'theme' => '.',
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $themeProjectDir) {
                chdir($themeProjectDir);
                $projectDirname = basename($themeProjectDir);

                $wpLoader->_initialize();
                Assert::assertEquals($projectDirname, get_option('stylesheet'));
                Assert::assertEquals($projectDirname, get_option('template'));
                Assert::assertTrue(function_exists('my_theme_setup'));
                Assert::assertEquals('1', get_option('my_theme_setup_ran'));
                global $wp_stylesheet_path, $wp_template_path;
                Assert::assertEquals($themeProjectDir, $wp_stylesheet_path);
                Assert::assertEquals($themeProjectDir, $wp_template_path);
                $theme = wp_get_theme();
                Assert::assertInstanceOf(\WP_Theme::class, $theme);
                Assert::assertEquals('My Theme', $theme->get('Name'));
                Assert::assertEmpty($theme->errors());
                Assert::assertTrue($theme->exists());
            }
        );

        // Reconfigure to use a theme from an absolute path.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'tablePrefix' => 'test_',
            'theme' => $themeProjectDir . '/vendor/acme/some-theme',
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $themeProjectDir) {
                chdir($themeProjectDir);

                $wpLoader->_initialize();

                Assert::assertEquals('some-theme', get_option('stylesheet'));
                Assert::assertEquals('some-theme', get_option('stylesheet'));
                Assert::assertTrue(function_exists('acme_some_theme_setup'));
                Assert::assertEquals('1', get_option('acme_some_theme_setup_ran'));
                global $wp_stylesheet_path, $wp_template_path;
                Assert::assertEquals($themeProjectDir . '/vendor/acme/some-theme', $wp_stylesheet_path);
                Assert::assertEquals($themeProjectDir . '/vendor/acme/some-theme', $wp_template_path);
                $theme = wp_get_theme();
                Assert::assertInstanceOf(\WP_Theme::class, $theme);
                Assert::assertEquals('Acme Some Theme', $theme->get('Name'));
                Assert::assertEmpty($theme->errors());
                Assert::assertTrue($theme->exists());
            }
        );

        // Reconfigure to use a theme from a relative path.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'tablePrefix' => 'test_',
            'theme' => 'vendor/acme/some-theme',
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $themeProjectDir) {
                chdir($themeProjectDir);

                $wpLoader->_initialize();

                Assert::assertEquals('some-theme', get_option('stylesheet'));
                Assert::assertEquals('some-theme', get_option('stylesheet'));
                Assert::assertTrue(function_exists('acme_some_theme_setup'));
                Assert::assertEquals('1', get_option('acme_some_theme_setup_ran'));
                global $wp_stylesheet_path, $wp_template_path;
                Assert::assertEquals($themeProjectDir . '/vendor/acme/some-theme', $wp_stylesheet_path);
                Assert::assertEquals($themeProjectDir . '/vendor/acme/some-theme', $wp_template_path);
            }
        );
    }

    private function module(array $moduleContainerConfig = [], ?array $moduleConfig = null): WPLoader
    {
        $this->mockModuleContainer = new ModuleContainer(new Di(), $moduleContainerConfig);
        return new WPLoader($this->mockModuleContainer, ($moduleConfig ?? $this->config));
    }

    /**
     * It should allow loading theme from arbitrary location in multisite
     *
     * @test
     */
    public function should_allow_loading_theme_from_arbitrary_location_in_multisite(): void
    {
        $themeProjectDir = FS::tmpDir('wploader_', [
            'style.css' => <<<CSS
/*
Theme Name: My Theme
Author: My Author
Description: My Description
Version: 1.0.0
Tested up to: 5.9
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
CSS
,
            'functions.php' => <<<PHP
<?php
function my_theme_setup() {
    update_option('my_theme_setup_ran', '1');
}

add_action('after_setup_theme','my_theme_setup' );
PHP
,
            'index.php' => '<?php // This file is required for the theme to work. ?>',
            'var' => [
                'wordpress' => []
            ],
            'vendor' => [
                'acme' => [
                    'some-theme' => [
                        'style.css' => <<<CSS
/*
Theme Name: Acme Some Theme
Author: My Author
Description: My Description
Version: 1.0.0
Tested up to: 5.9
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
CSS
,
                        'functions.php' => <<<PHP
<?php
function acme_some_theme_setup() {
    update_option('acme_some_theme_setup_ran', '1');
}

add_action('after_setup_theme','acme_some_theme_setup' );
PHP
,
                        'index.php' => '<?php // This file is required for the theme to work. ?>',
                    ]
                ]
            ]
        ]);
        $wpRootDir = $themeProjectDir . '/var/wordpress';
        Installation::scaffold($wpRootDir, '6.1.1');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $this->config = [
            'multisite' => true,
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'tablePrefix' => 'test_',
            'theme' => $themeProjectDir,
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $themeProjectDir) {
                chdir($themeProjectDir);
                $themeName = basename($themeProjectDir);

                $wpLoader->_initialize();

                Assert::assertEquals($themeName, get_option('stylesheet'));
                Assert::assertEquals($themeName, get_option('template'));
                Assert::assertTrue(function_exists('my_theme_setup'));
                Assert::assertEquals('1', get_option('my_theme_setup_ran'));
                global $wp_stylesheet_path, $wp_template_path;
                Assert::assertEquals($themeProjectDir, $wp_stylesheet_path);
                Assert::assertEquals($themeProjectDir, $wp_template_path);
                $theme = wp_get_theme();
                Assert::assertInstanceOf(\WP_Theme::class, $theme);
                Assert::assertEquals('My Theme', $theme->get('Name'));
                Assert::assertEmpty($theme->errors());
                Assert::assertTrue($theme->exists());
            }
        );

        // Reconfigure the module to use a relative path.
        $this->config = [
            'multisite' => true,
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'tablePrefix' => 'test_',
            'theme' => '.',
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $themeProjectDir) {
                chdir($themeProjectDir);
                $projectDirname = basename($themeProjectDir);

                $wpLoader->_initialize();
                Assert::assertEquals($projectDirname, get_option('stylesheet'));
                Assert::assertEquals($projectDirname, get_option('template'));
                Assert::assertTrue(function_exists('my_theme_setup'));
                Assert::assertEquals('1', get_option('my_theme_setup_ran'));
                global $wp_stylesheet_path, $wp_template_path;
                Assert::assertEquals($themeProjectDir, $wp_stylesheet_path);
                Assert::assertEquals($themeProjectDir, $wp_template_path);
                $theme = wp_get_theme();
                Assert::assertInstanceOf(\WP_Theme::class, $theme);
                Assert::assertEquals('My Theme', $theme->get('Name'));
                Assert::assertEmpty($theme->errors());
                Assert::assertTrue($theme->exists());
            }
        );

        // Reconfigure to use a theme from an absolute path.
        $this->config = [
            'multisite' => true,
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'tablePrefix' => 'test_',
            'theme' => $themeProjectDir . '/vendor/acme/some-theme',
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $themeProjectDir) {
                chdir($themeProjectDir);

                $wpLoader->_initialize();

                Assert::assertEquals('some-theme', get_option('stylesheet'));
                Assert::assertEquals('some-theme', get_option('stylesheet'));
                Assert::assertTrue(function_exists('acme_some_theme_setup'));
                Assert::assertEquals('1', get_option('acme_some_theme_setup_ran'));
                global $wp_stylesheet_path, $wp_template_path;
                Assert::assertEquals($themeProjectDir . '/vendor/acme/some-theme', $wp_stylesheet_path);
                Assert::assertEquals($themeProjectDir . '/vendor/acme/some-theme', $wp_template_path);
                $theme = wp_get_theme();
                Assert::assertInstanceOf(\WP_Theme::class, $theme);
                Assert::assertEquals('Acme Some Theme', $theme->get('Name'));
                Assert::assertEmpty($theme->errors());
                Assert::assertTrue($theme->exists());
            }
        );

        // Reconfigure to use a theme from a relative path.
        $this->config = [
            'multisite' => true,
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'tablePrefix' => 'test_',
            'theme' => 'vendor/acme/some-theme',
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $themeProjectDir) {
                chdir($themeProjectDir);

                $wpLoader->_initialize();

                Assert::assertEquals('some-theme', get_option('stylesheet'));
                Assert::assertEquals('some-theme', get_option('stylesheet'));
                Assert::assertTrue(function_exists('acme_some_theme_setup'));
                Assert::assertEquals('1', get_option('acme_some_theme_setup_ran'));
                global $wp_stylesheet_path, $wp_template_path;
                Assert::assertEquals($themeProjectDir . '/vendor/acme/some-theme', $wp_stylesheet_path);
                Assert::assertEquals($themeProjectDir . '/vendor/acme/some-theme', $wp_template_path);
            }
        );
    }

public function invalidThemeConfigurationDataProvider(): array
    {
        return [
            'int' => [1],
            'float' => [1.1],
            'bool' => [true],
            'object' => [new \stdClass()],
            'array' => [[]],
            'associative array' => [['foo' => 'bar']],
        ];
    }

    /**
     * It should throw if theme parameter configured with not an array of two strings
     *
     * @test
     * @dataProvider invalidThemeConfigurationDataProvider
     */
    public function should_throw_if_theme_parameter_configured_with_not_an_array_of_two_strings($theme): void
    {
        $this->expectException(ModuleConfigException::class);

        $this->config = [
            'multisite' => true,
            'wpRootFolder' => __DIR__,
            'dbUrl' => 'mysql://root:root@mysql:3306/wordpress',
            'tablePrefix' => 'test_',
            'theme' => $theme
        ];

        $this->module();
    }

    /**
     * It should allow loading parent and child theme from arbitrary paths
     *
     * @test
     */
    public function should_allow_loading_parent_and_child_theme_from_arbitrary_paths(): void
    {
        $childThemeDir = FS::tmpDir('wploader_', [
            'style.css' => <<<CSS
/*
Theme Name: My Child Theme
Template: parent-theme
Author: My Author
Description: My Description
Version: 1.0.0
Tested up to: 5.9
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
CSS
,
            'functions.php' => <<<PHP
<?php
function my_child_theme_setup() {
    update_option('my_child_theme_setup_ran', '1');
}

add_action('after_setup_theme','my_child_theme_setup' );
PHP
,
            'index.php' => '<?php // This file is required for the theme to work. ?>',
            'var' => [
                'wordpress' => []
            ],
            'vendor' => [
                'acme' => [
                    'parent-theme' => [
                        'style.css' => <<<CSS
/*
Theme Name: Acme Parent Theme
Author: My Author
Description: My Description
Version: 1.0.0
Tested up to: 5.9
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
CSS
,
                        'functions.php' => <<<PHP
<?php
function acme_parent_theme_setup() {
    update_option('acme_parent_theme_setup_ran', '1');
}

add_action('after_setup_theme','acme_parent_theme_setup' );
PHP
,
                        'index.php' => '<?php // This file is required for the theme to work. ?>',
                    ]
                ]
            ]
        ]);
        $wpRootDir = $childThemeDir . '/var/wordpress';
        $parentThemeDir = $childThemeDir . '/vendor/acme/parent-theme';
        Installation::scaffold($wpRootDir, '6.1.1');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'tablePrefix' => 'test_',
            'theme' => ['vendor/acme/parent-theme', $childThemeDir]
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $parentThemeDir, $childThemeDir) {
                chdir($childThemeDir);
                $parentThemeName = basename($parentThemeDir);
                $childThemeName = basename($childThemeDir);

                $wpLoader->_initialize();

                Assert::assertEquals($parentThemeName, get_option('template'));
                Assert::assertEquals($childThemeName, get_option('stylesheet'));
                Assert::assertTrue(function_exists('acme_parent_theme_setup'));
                Assert::assertTrue(function_exists('my_child_theme_setup'));
                Assert::assertEquals('1', get_option('acme_parent_theme_setup_ran'));
                Assert::assertEquals('1', get_option('my_child_theme_setup_ran'));
                global $wp_template_path, $wp_stylesheet_path;
                Assert::assertEquals($parentThemeDir, $wp_template_path);
                Assert::assertEquals($childThemeDir, $wp_stylesheet_path);
                $theme = wp_get_theme();
                Assert::assertInstanceOf(\WP_Theme::class, $theme);
                Assert::assertEquals('My Child Theme', $theme->get('Name'));
                Assert::assertEquals('parent-theme', $theme->get('Template'));
                Assert::assertEmpty($theme->errors());
                Assert::assertTrue($theme->exists());
            }
        );

        // Reconfigure and load the child theme from the current directory.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'tablePrefix' => 'test_',
            'theme' => ['vendor/acme/parent-theme', '.']
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $parentThemeDir, $childThemeDir) {
                chdir($childThemeDir);
                $parentThemeName = basename($parentThemeDir);
                $childThemeName = basename($childThemeDir);

                $wpLoader->_initialize();

                Assert::assertEquals($parentThemeName, get_option('template'));
                Assert::assertEquals($childThemeName, get_option('stylesheet'));
                Assert::assertTrue(function_exists('acme_parent_theme_setup'));
                Assert::assertTrue(function_exists('my_child_theme_setup'));
                Assert::assertEquals('1', get_option('acme_parent_theme_setup_ran'));
                Assert::assertEquals('1', get_option('my_child_theme_setup_ran'));
                global $wp_template_path, $wp_stylesheet_path;
                Assert::assertEquals($parentThemeDir, $wp_template_path);
                Assert::assertEquals($childThemeDir, $wp_stylesheet_path);
                $theme = wp_get_theme();
                Assert::assertInstanceOf(\WP_Theme::class, $theme);
                Assert::assertEquals('My Child Theme', $theme->get('Name'));
                Assert::assertEquals('parent-theme', $theme->get('Template'));
                Assert::assertEmpty($theme->errors());
                Assert::assertTrue($theme->exists());
            }
        );
    }

    /**
     * It should allow loading parent and child theme from arbitrary paths in multisite
     *
     * @test
     */
    public function should_allow_loading_parent_and_child_theme_from_arbitrary_paths_in_multisite(): void
    {
        $childThemeDir = FS::tmpDir('wploader_', [
            'style.css' => <<<CSS
/*
Theme Name: My Child Theme
Template: parent-theme
Author: My Author
Description: My Description
Version: 1.0.0
Tested up to: 5.9
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
CSS
,
            'functions.php' => <<<PHP
<?php
function my_child_theme_setup() {
    update_option('my_child_theme_setup_ran', '1');
}

add_action('after_setup_theme','my_child_theme_setup' );
PHP
,
            'index.php' => '<?php // This file is required for the theme to work. ?>',
            'var' => [
                'wordpress' => []
            ],
            'vendor' => [
                'acme' => [
                    'parent-theme' => [
                        'style.css' => <<<CSS
/*
Theme Name: Acme Parent Theme
Author: My Author
Description: My Description
Version: 1.0.0
Tested up to: 5.9
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
CSS
,
                        'functions.php' => <<<PHP
<?php
function acme_parent_theme_setup() {
    update_option('acme_parent_theme_setup_ran', '1');
}

add_action('after_setup_theme','acme_parent_theme_setup' );
PHP
,
                        'index.php' => '<?php // This file is required for the theme to work. ?>',
                    ]
                ]
            ]
        ]);
        $wpRootDir = $childThemeDir . '/var/wordpress';
        $parentThemeDir = $childThemeDir . '/vendor/acme/parent-theme';
        Installation::scaffold($wpRootDir, '6.1.1');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $this->config = [
            'multisite' => true,
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'tablePrefix' => 'test_',
            'theme' => ['vendor/acme/parent-theme', $childThemeDir]
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $parentThemeDir, $childThemeDir) {
                chdir($childThemeDir);
                $parentThemeName = basename($parentThemeDir);
                $childThemeName = basename($childThemeDir);

                $wpLoader->_initialize();

                Assert::assertEquals($parentThemeName, get_option('template'));
                Assert::assertEquals($childThemeName, get_option('stylesheet'));
                Assert::assertTrue(function_exists('acme_parent_theme_setup'));
                Assert::assertTrue(function_exists('my_child_theme_setup'));
                Assert::assertEquals('1', get_option('acme_parent_theme_setup_ran'));
                Assert::assertEquals('1', get_option('my_child_theme_setup_ran'));
                global $wp_template_path, $wp_stylesheet_path;
                Assert::assertEquals($parentThemeDir, $wp_template_path);
                Assert::assertEquals($childThemeDir, $wp_stylesheet_path);
                $theme = wp_get_theme();
                Assert::assertInstanceOf(\WP_Theme::class, $theme);
                Assert::assertEquals('My Child Theme', $theme->get('Name'));
                Assert::assertEquals('parent-theme', $theme->get('Template'));
                Assert::assertEmpty($theme->errors());
                Assert::assertTrue($theme->exists());
            }
        );

        // Reconfigure and load the child theme from the current directory.
        $this->config = [
            'multisite' => true,
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'tablePrefix' => 'test_',
            'theme' => ['vendor/acme/parent-theme', '.']
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $parentThemeDir, $childThemeDir) {
                chdir($childThemeDir);
                $parentThemeName = basename($parentThemeDir);
                $childThemeName = basename($childThemeDir);

                $wpLoader->_initialize();

                Assert::assertEquals($parentThemeName, get_option('template'));
                Assert::assertEquals($childThemeName, get_option('stylesheet'));
                Assert::assertTrue(function_exists('acme_parent_theme_setup'));
                Assert::assertTrue(function_exists('my_child_theme_setup'));
                Assert::assertEquals('1', get_option('acme_parent_theme_setup_ran'));
                Assert::assertEquals('1', get_option('my_child_theme_setup_ran'));
                global $wp_template_path, $wp_stylesheet_path;
                Assert::assertEquals($parentThemeDir, $wp_template_path);
                Assert::assertEquals($childThemeDir, $wp_stylesheet_path);
                $theme = wp_get_theme();
                Assert::assertInstanceOf(\WP_Theme::class, $theme);
                Assert::assertEquals('My Child Theme', $theme->get('Name'));
                Assert::assertEquals('parent-theme', $theme->get('Template'));
                Assert::assertEmpty($theme->errors());
                Assert::assertTrue($theme->exists());
            }
        );
    }
}
