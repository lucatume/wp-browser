<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
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
class WPLoaderArbitraryPluginLocationTest extends Unit
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

    private function module(array $moduleContainerConfig = [], ?array $moduleConfig = null): WPLoader
    {
        $this->mockModuleContainer = new ModuleContainer(new Di(), $moduleContainerConfig);
        return new WPLoader($this->mockModuleContainer, ($moduleConfig ?? $this->config));
    }

    public function test_loads_plugins_from_default_location_correctly(): void
    {
        $projectDir = FS::tmpDir('wploader_');
        $installation = Installation::scaffold($projectDir);
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $installationDb = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        if (!mkdir($projectDir . '/wp-content/plugins/test-one', 0777, true)) {
            throw new \RuntimeException('Unable to create test directory for plugin test-one');
        }
        if (!file_put_contents(
            $projectDir . '/wp-content/plugins/test-one/plugin.php',
            <<< PHP
<?php
/**
 * Plugin Name: Test One
 */
 
 function test_one_loaded(){}
PHP
        )) {
            throw new \RuntimeException('Unable to create test plugin file for plugin test-one');
        }
        if (!mkdir($projectDir . '/wp-content/plugins/test-two', 0777, true)) {
            throw new \RuntimeException('Unable to create test directory for plugin test-two');
        }
        if (!file_put_contents(
            $projectDir . '/wp-content/plugins/test-two/plugin.php',
            <<< PHP
<?php
/**
 * Plugin Name: Test Two
 */
 
 function test_two_loaded(){}
PHP
        )) {
            throw new \RuntimeException('Unable to create test plugin file for plugin test-two');
        }

        $this->config = [
            'wpRootFolder' => $projectDir,
            'dbUrl' => $installationDb->getDbUrl(),
            'tablePrefix' => 'test_',
            'plugins' => [
                'test-one/plugin.php',
                'test-two/plugin.php',
            ]
        ];
        $wpLoader = $this->module();
        $projectDirname = basename($projectDir);

        $this->assertInIsolation(
            static function () use ($wpLoader, $projectDir) {
                chdir($projectDir);

                $wpLoader->_initialize();

                Assert::assertTrue(function_exists('test_one_loaded'));
                Assert::assertTrue(function_exists('test_two_loaded'));
            }
        );
    }

    /**
     * It should allow loading a plugin from an arbitrary path
     *
     * @test
     */
    public function should_allow_loading_a_plugin_from_an_arbitrary_path(): void
    {
        $myPluginCode = <<< PHP
<?php
/** Plugin Name: My Plugin */
function my_plugin_main() { }

register_activation_hook( __FILE__, 'activate_my_plugin' );
function activate_my_plugin(){
    update_option('my_plugin_activated', '1');
}
PHP;
        $projectDir = FS::tmpDir('wploader_', [
            'my-plugin.php' => $myPluginCode,

            'var' => [
                'wordpress' => []
            ],
            'vendor' => [
                'acme' => [

                ]
            ]
        ]);
        $wpRootDir = $projectDir . '/var/wordpress';
        $installation = Installation::scaffold($wpRootDir);
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $installationDb = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        // Copy WooCommerce from the main installation to a temporary directory.
        $tmpDir = sys_get_temp_dir();
        $mainWPInstallationRootDir = Env::get('WORDPRESS_ROOT_DIR');
        if (!FS::recurseCopy(
            $mainWPInstallationRootDir . '/wp-content/plugins/woocommerce',
            $tmpDir . '/external-woocommerce'
        )) {
            throw new \RuntimeException('Could not copy plugin woocommerce');
        }
        $externalAbsolutePathPluginDir = $tmpDir . '/external-woocommerce';
        $this->assertFileExists($externalAbsolutePathPluginDir . '/woocommerce.php');
        if (!FS::recurseCopy(
            codecept_data_dir('plugins/some-external-plugin'),
            $projectDir . '/vendor/acme/some-external-plugin'
        )) {
            throw new \RuntimeException('Could not copy plugin some-external-plugin');
        }

        $hash = md5(microtime());
        $externalExplodingPlugin = sys_get_temp_dir() . '/' . $hash . '/exploding-plugin';
        if (!(mkdir($externalExplodingPlugin, 0777, true) && is_dir($externalExplodingPlugin))) {
            throw new \RuntimeException('Could not create exploding plugin directory');
        }
        if (!copy(codecept_data_dir('plugins/exploding-plugin/main.php'), $externalExplodingPlugin . '/main.php')) {
            throw new \RuntimeException('Could not copy exploding plugin file');
        }
        $testPluginFileContents = <<< PHP
<?php
/** Plugin Name: Test Plugin */
function test_plugin_main() { }

register_activation_hook( __FILE__, 'test_plugin_activate' );
function test_plugin_activate() {
    update_option('test_plugin_activated', '1');
}
PHP;

        if (!file_put_contents($wpRootDir . '/wp-content/plugins/test.php', $testPluginFileContents)) {
            throw new \RuntimeException('Could not write test.php plugin file');
        }

        // Create a configuration to load the plugins from arbitrary paths.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $installationDb->getDbUrl(),
            'tablePrefix' => 'test_',
            'plugins' => [
                'test.php', // From the WordPress installation plugins directory.
                $externalAbsolutePathPluginDir . '/woocommerce.php', // Absolute path.
                'vendor/acme/some-external-plugin/some-plugin.php', // Relative path to the project root folder.
                'my-plugin.php' // Relative path to the project root folder, development plugin file.
            ],
            'silentlyActivatePlugins' => [
                $externalExplodingPlugin . '/main.php' // Absolute path.
            ]
        ];

        $wpLoader = $this->module();
        $projectDirname = basename($projectDir);

        $this->assertInIsolation(
            static function () use ($wpLoader, $projectDir) {
                chdir($projectDir);
                $projectDirname = basename($projectDir);

                $wpLoader->_initialize();

                Assert::assertEquals([
                    'test.php',
                    'external-woocommerce/woocommerce.php',
                    'some-external-plugin/some-plugin.php',
                    "$projectDirname/my-plugin.php",
                    'exploding-plugin/main.php'
                ], get_option('active_plugins'));

                // Test plugin from the WordPress installation plugins directory.
                Assert::assertEquals('1', get_option('test_plugin_activated'));
                Assert::assertTrue(function_exists('test_plugin_main'));

                // WooCommerce from the absolute path.
                Assert::assertTrue(function_exists('wc_get_product'));
                Assert::assertTrue(class_exists('WC_Product'));
                $product = new \WC_Product();
                $product->set_name('Test Product');
                $product->set_price(10);
                $product->set_status('publish');
                $product->save();
                Assert::assertInstanceOf(\WC_Product::class, $product);
                Assert::assertInstanceOf(\WC_Product::class, wc_get_product($product->get_id()));

                // Some external plugin from the relative path.
                Assert::assertTrue(function_exists('some_plugin_main'));
                Assert::assertEquals('1', get_option('some_plugin_activated'));

                // My plugin from the relative path.
                Assert::assertTrue(function_exists('my_plugin_main'));
                Assert::assertEquals('1', get_option('my_plugin_activated'));

                // Exploding plugin from the absolute path.
                Assert::assertTrue(function_exists('exploding_plugin_main'));
                Assert::assertEquals('', get_option('exploding_plugin_activated'));
            }
        );
    }

    /**
     * It should allow loading a plugin from an arbitrary path in multisite
     *
     * @test
     */
    public function should_allow_loading_a_plugin_from_an_arbitrary_path_in_multisite(): void
    {
        $myPluginCode = <<< PHP
<?php
/** Plugin Name: My Plugin */
function my_plugin_main() { }

register_activation_hook( __FILE__, 'activate_my_plugin' );
function activate_my_plugin(){
    update_option('my_plugin_activated', '1');
}
PHP;
        $projectDir = FS::tmpDir('wploader_', [
            'my-plugin.php' => $myPluginCode,

            'var' => [
                'wordpress' => []
            ],
            'vendor' => [
                'acme' => [

                ]
            ]
        ]);
        $wpRootDir = $projectDir . '/var/wordpress';
        $installation = Installation::scaffold($wpRootDir);
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $installationDb = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'wp_');
        // Copy WooCommerce from the main installation to a temporary directory.
        $tmpDir = sys_get_temp_dir();
        $mainWPInstallationRootDir = Env::get('WORDPRESS_ROOT_DIR');
        if (!FS::recurseCopy(
            $mainWPInstallationRootDir . '/wp-content/plugins/woocommerce',
            $tmpDir . '/external-woocommerce'
        )) {
            throw new \RuntimeException('Could not copy plugin woocommerce');
        }
        $externalAbsolutePathPluginDir = $tmpDir . '/external-woocommerce';
        $this->assertFileExists($externalAbsolutePathPluginDir . '/woocommerce.php');
        if (!FS::recurseCopy(
            codecept_data_dir('plugins/some-external-plugin'),
            $projectDir . '/vendor/acme/some-external-plugin'
        )) {
            throw new \RuntimeException('Could not copy plugin some-external-plugin');
        }

        $hash = md5(microtime());
        $externalExplodingPlugin = sys_get_temp_dir() . '/' . $hash . '/exploding-plugin';
        if (!(mkdir($externalExplodingPlugin, 0777, true) && is_dir($externalExplodingPlugin))) {
            throw new \RuntimeException('Could not create exploding plugin directory');
        }
        if (!copy(codecept_data_dir('plugins/exploding-plugin/main.php'), $externalExplodingPlugin . '/main.php')) {
            throw new \RuntimeException('Could not copy exploding plugin file');
        }
        $testPluginFileContents = <<< PHP
<?php
/** Plugin Name: Test Plugin */
function test_plugin_main() { }

register_activation_hook( __FILE__, 'test_plugin_activate' );
function test_plugin_activate() {
    update_option('test_plugin_activated', '1');
}
PHP;

        if (!file_put_contents($wpRootDir . '/wp-content/plugins/test.php', $testPluginFileContents)) {
            throw new \RuntimeException('Could not write test.php plugin file');
        }

        // Create a configuration to load the plugins from arbitrary paths.
        $this->config = [
            'multisite' => true,
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $installationDb->getDbUrl(),
            'tablePrefix' => 'test_',
            'plugins' => [
                'test.php', // From the WordPress installation plugins directory.
                $externalAbsolutePathPluginDir . '/woocommerce.php', // Absolute path.
                'vendor/acme/some-external-plugin/some-plugin.php', // Relative path to the project root folder.
                'my-plugin.php' // Relative path to the project root folder, development plugin file.
            ],
            'silentlyActivatePlugins' => [
                $externalExplodingPlugin . '/main.php' // Absolute path.
            ]
        ];

        $wpLoader = $this->module();

        $this->assertInIsolation(
            static function () use ($wpLoader, $projectDir) {
                chdir($projectDir);
                $projectDirname = basename($projectDir);

                $wpLoader->_initialize();

                Assert::assertEquals([
                    'test.php',
                    'external-woocommerce/woocommerce.php',
                    'some-external-plugin/some-plugin.php',
                    "$projectDirname/my-plugin.php",
                    'exploding-plugin/main.php'
                ], array_keys(get_site_option('active_sitewide_plugins')));

                // Test plugin from the WordPress installation plugins directory.
                Assert::assertEquals('1', get_option('test_plugin_activated'));
                Assert::assertTrue(function_exists('test_plugin_main'));

                // WooCommerce from the absolute path.
                Assert::assertTrue(function_exists('wc_get_product'));
                Assert::assertTrue(class_exists('WC_Product'));
                $product = new \WC_Product();
                $product->set_name('Test Product');
                $product->set_price(10);
                $product->set_status('publish');
                $product->save();
                Assert::assertInstanceOf(\WC_Product::class, $product);
                Assert::assertInstanceOf(\WC_Product::class, wc_get_product($product->get_id()));

                // Some external plugin from the relative path.
                Assert::assertTrue(function_exists('some_plugin_main'));
                Assert::assertEquals('1', get_option('some_plugin_activated'));

                // My plugin from the relative path.
                Assert::assertTrue(function_exists('my_plugin_main'));
                Assert::assertEquals('1', get_option('my_plugin_activated'));

                // Exploding plugin from the absolute path.
                Assert::assertTrue(function_exists('exploding_plugin_main'));
                Assert::assertEquals('', get_option('exploding_plugin_activated'));
            }
        );
    }
}
