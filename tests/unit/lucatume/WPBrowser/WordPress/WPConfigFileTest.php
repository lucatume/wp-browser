<?php

namespace lucatume\WPBrowser\WordPress;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class WPConfigFileTest extends Unit
{
    private string|null $isDdevProjectEnvVar = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->isDdevProjectEnvVar = (string)getenv('IS_DDEV_PROJECT');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        putenv('IS_DDEV_PROJECT=' . (string)$this->isDdevProjectEnvVar);
    }


    /**
     * It should throw if building on non existing root directory
     *
     * @test
     */
    public function should_throw_if_building_on_non_existing_root_directory(): void
    {
        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::ROOT_DIR_NOT_FOUND);

        new WPConfigFile('/non-existing-dir', '/non-existing-dir/wp-config.php');
    }

    /**
     * It should throw if wp-config.php file not found in root directory
     *
     * @test
     */
    public function should_throw_if_wp_config_php_file_not_found_in_root_directory()
    {
        $wpRootDir = FS::tmpDir('wp-config_', [
            'wp-settings.php' => '<?php echo "Hello there!";'
        ]);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WP_CONFIG_FILE_NOT_FOUND);

        new WPConfigFile($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if wp-settings.php file not found in root directory
     *
     * @test
     */
    public function should_throw_if_wp_settings_php_file_not_found_in_root_directory(): void
    {
        $wpRootDir = FS::tmpDir('wp-config_', [
            'wp-config.php' => '<?php echo "Hello there!";'
        ]);

        $this->expectException(InstallationException::class);
        $this->expectExceptionCode(InstallationException::WP_SETTINGS_FILE_NOT_FOUND);

        new WPConfigFile($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if wp-config.php file is malformed
     *
     * @test
     */
    public function should_throw_if_wp_config_php_file_is_malformed(): void
    {
        $wpRootDir = FS::tmpDir('wp-config_', [
            'wp-settings.php' => '<?php echo "Hello there!";',
            'wp-config.php' => '<?php $foo[23] = bar";'
        ]);

        $this->expectException(ProcessException::class);

        new WPConfigFile($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if wp-config.php file exits non 0
     *
     * @test
     */
    public function should_throw_if_wp_config_php_file_exits_non_0(): void
    {
        $wpRootDir = FS::tmpDir('wp-config_', [
            'wp-settings.php' => '<?php echo "Hello there!";',
            'wp-config.php' => '<?php exit(23);'
        ]);

        $this->expectException(ProcessException::class);

        new WPConfigFile($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should throw if wp-config.php return non-array value
     *
     * @test
     */
    public function should_throw_if_wp_config_php_return_non_array_value(): void
    {
        $wpRootDir = FS::tmpDir('wp-config_', [
            'wp-settings.php' => '<?php echo "Hello there!";',
            'wp-config.php' => '<?php die("test");'
        ]);

        $this->expectException(ProcessException::class);

        new WPConfigFile($wpRootDir, $wpRootDir . '/wp-config.php');
    }

    /**
     * It should read vars defined in the wp-config.php file
     *
     * @test
     */
    public function should_read_vars_defined_in_the_wp_config_php_file(): void
    {
        $wpConfigFileCode = <<< PHP
<?php
\$var1 = 23;
\$var2 = 'foo';
\$var3 = 2389;
\$var4 = ['foo' => 23, 'bar'=> '89'];
PHP;

        $wpRootDir = FS::tmpDir('wp-config_', [
            'wp-settings.php' => '<?php echo "Hello there!";',
            'wp-config.php' => $wpConfigFileCode
        ]);

        $wpConfigFile = new WPConfigFile($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals(23, $wpConfigFile->getVar('var1'));
        $this->assertEquals('foo', $wpConfigFile->getVar('var2'));
        $this->assertEquals(2389, $wpConfigFile->getVar('var3'));
        $this->assertEquals(['foo' => 23, 'bar'=> '89'], $wpConfigFile->getVar('var4'));
        $this->assertFalse($wpConfigFile->issetVar('var5'));
        $this->assertNull($wpConfigFile->getVar('var5'));
    }

    /**
     * It should read constants defined in the wp-config.php file
     *
     * @test
     */
    public function should_read_constants_defined_in_the_wp_config_php_file()
    {
        $wpConfigFileCode = <<< PHP
<?php
const VAR1 = 23;
const VAR2 = 'foo';
const VAR3 = 2389;
const VAR4 = ['foo' => 23, 'bar'=> '89'];
PHP;

        $wpRootDir = FS::tmpDir('wp-config_', [
            'wp-settings.php' => '<?php echo "Hello there!";',
            'wp-config.php' => $wpConfigFileCode
        ]);

        $wpConfigFile = new WPConfigFile($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals(23, $wpConfigFile->getConstant('VAR1'));
        $this->assertEquals('foo', $wpConfigFile->getConstant('VAR2'));
        $this->assertEquals(2389, $wpConfigFile->getConstant('VAR3'));
        $this->assertEquals(['foo' => 23, 'bar'=> '89'], $wpConfigFile->getConstant('VAR4'));
        $this->assertFalse($wpConfigFile->isDefinedConst('VAR5'));
        $this->assertNull($wpConfigFile->getConstant('VAR5'));
        $this->assertEquals([
            'VAR1' => 23,
            'VAR2' => 'foo',
            'VAR3' => 2389,
            'VAR4' => ['foo' => 23, 'bar' => '89'],
        ], $wpConfigFile->getConstants());
    }

    /**
     * It should detect use of MySQL database
     *
     * @test
     */
    public function should_detect_use_of_my_sql_database(): void
    {
        $wpConfigFileCode = <<< PHP
<?php
define('DB_NAME', 'wordpress');
define('DB_USER', 'wordpress');
define('DB_PASSWORD', 'wordpress');
define('DB_HOST', 'localhost:3306');
PHP;

        $wpRootDir = FS::tmpDir('wp-config_', [
            'wp-settings.php' => '<?php echo "Hello there!";',
            'wp-config.php' => $wpConfigFileCode
        ]);

        $wpConfigFile = new WPConfigFile($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertTrue($wpConfigFile->usesMySQL());
        $this->assertFalse($wpConfigFile->usesSQLite());
    }

    /**
     * It should detect use of SQLite database
     *
     * @test
     */
    public function should_detect_use_of_sq_lite_database(): void
    {
        $wpConfigFileCode = <<< PHP
<?php
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASSWORD', '');
define('DB_HOST', '');
define('DB_DIR', __DIR__. '/wp-content');
define('DB_FILE', '/db.sqlite');
PHP;

        $wpRootDir = FS::tmpDir('wp-config_', [
            'wp-settings.php' => '<?php echo "Hello there!";',
            'wp-config.php' => $wpConfigFileCode
        ]);

        $wpConfigFile = new WPConfigFile($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertFalse($wpConfigFile->usesMySQL());
        $this->assertTrue($wpConfigFile->usesSQLite());
    }

    public function test_it_inherits_env():void{
        $configFile = codecept_data_dir('wp-config-files/ddev/wp-config.php');
        putenv('IS_DDEV_PROJECT=true');

        $wpConfigFile = new WPConfigFile(dirname($configFile), $configFile);

        $this->assertEquals($wpConfigFile->getConstant('DB_NAME'),'db');
    }
}
