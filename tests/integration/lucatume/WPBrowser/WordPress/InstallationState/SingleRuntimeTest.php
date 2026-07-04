<?php


namespace lucatume\WPBrowser\WordPress\InstallationState;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Tests\Traits\FastScaffold;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Traits\UopzFunctions;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\ConfigurationData;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Database\SQLiteDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\InstallationException;


class SingleRuntimeTest extends \Codeception\Test\Unit
{
    use \lucatume\WPBrowser\Traits\UopzFunctions;
    use \lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
    use \lucatume\WPBrowser\Tests\Traits\FastScaffold;

    /**
     * It should allow getting information about the installation
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_allow_getting_information_about_the_installation(): void
    {
        $wpRootDir = FS::tmpDir('single_j');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $this->fastScaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertFalse($single->isMultisite());
        $this->assertEquals($wpRootDir . '/', $single->getWpRootDir());
        $this->assertEquals($wpRootDir . '/wp-config.php', $single->getWpRootDir('wp-config.php'));
        $this->assertEquals($wpRootDir . '/wp-config.php', $single->getWpRootDir('/wp-config.php'));
        $this->assertEquals($wpRootDir . '/wp-config.php', $single->getWpConfigPath());
        $this->assertTrue(strlen($single->getAuthKey()) === 64 && $single->getAuthKey() !== $single->getSecureAuthKey());
        $this->assertTrue(strlen($single->getSecureAuthKey()) === 64 && $single->getSecureAuthKey() !== $single->getLoggedInKey());
        $this->assertTrue(strlen($single->getLoggedInKey()) === 64 && $single->getLoggedInKey() !== $single->getNonceKey());
        $this->assertTrue(strlen($single->getNonceKey()) === 64 && $single->getNonceKey() !== $single->getAuthSalt());
        $this->assertTrue(strlen($single->getAuthSalt()) === 64 && $single->getAuthSalt() !== $single->getSecureAuthSalt());
        $this->assertTrue(strlen($single->getSecureAuthSalt()) === 64 && $single->getSecureAuthSalt() !== $single->getLoggedInSalt());
        $this->assertTrue(strlen($single->getLoggedInSalt()) === 64 && $single->getLoggedInSalt() !== $single->getNonceSalt());
        $this->assertSame(64, strlen($single->getNonceSalt()));
        $this->assertEquals('test_', $single->getTablePrefix());
        $this->assertTrue($single->isConfigured());
        $this->assertEquals([
            'AUTH_KEY' => $single->getAuthKey(),
            'SECURE_AUTH_KEY' => $single->getSecureAuthKey(),
            'LOGGED_IN_KEY' => $single->getLoggedInKey(),
            'NONCE_KEY' => $single->getNonceKey(),
            'AUTH_SALT' => $single->getAuthSalt(),
            'SECURE_AUTH_SALT' => $single->getSecureAuthSalt(),
            'LOGGED_IN_SALT' => $single->getLoggedInSalt(),
            'NONCE_SALT' => $single->getNonceSalt(),
        ], $single->getSalts());
    }

    /**
     * It should allow getting the db
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_allow_getting_the_db(): void
    {
        $wpRootDir = FS::tmpDir('single_k');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $this->fastScaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals($dbName, $single->getDb()->getDbName());
        $this->assertEquals($dbHost, $single->getDb()->getDbHost());
        $this->assertEquals($dbUser, $single->getDb()->getDbUser());
        $this->assertEquals($dbPassword, $single->getDb()->getDbPassword());
    }

    /**
     * It should allow setting the db
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_allow_setting_the_db(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );
        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');
        $dumpFile = $wpRootDir .'/dump.sql';
        $db->dump($dumpFile);

        // Create a new database and import the dump: still installed.
        $dbName2 = Random::dbName();
        $db2 = new MysqlDatabase($dbName2, $dbUser, $dbPassword, $dbHost);
        $db2->import($dumpFile);

        $withInstalledDb = $single->setDb($db2);

        $this->assertInstanceOf(Single::class, $withInstalledDb);
        $this->assertSame($db2, $withInstalledDb->getDb());

        Installation::placeSqliteMuPlugin($wpRootDir . '/wp-content/mu-plugins', $wpRootDir . '/wp-content');

        $sqliteDb = new SqliteDatabase($wpRootDir . '/wp-content', 'test.db');

        $withSqliteDb = $single->setDb($sqliteDb);

        $this->assertInstanceOf(Configured::class, $withSqliteDb);
        $this->assertSame($sqliteDb, $withSqliteDb->getDb());

        // Install using SQLite.
        $installedOnSqlite = $withSqliteDb->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test'
        );

        $this->assertInstanceOf(Single::class, $installedOnSqlite);

        $sqliteDb->dump($dumpFile);

        $sqliteDb2 = new SqliteDatabase($wpRootDir . '/wp-content', 'test2.db');
        $sqliteDb2->import($dumpFile);

        $withSqliteDb2 = $withSqliteDb->setDb($sqliteDb2);

        $this->assertInstanceOf(Single::class, $withSqliteDb2);
        $this->assertSame($sqliteDb2, $withSqliteDb2->getDb());
    }

    /**
     * It should allow getting the site constants
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_allow_getting_the_site_constants(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $this->fastScaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');
        $constants = $single->getConstants();

        $expected = [
            'DB_NAME' => $dbName,
            'DB_USER' => $dbUser,
            'DB_PASSWORD' => $dbPassword,
            'DB_HOST' => $dbHost,
            'DB_CHARSET' => 'utf8',
            'DB_COLLATE' => '',
            'AUTH_KEY' => $single->getAuthKey(),
            'SECURE_AUTH_KEY' => $single->getSecureAuthKey(),
            'LOGGED_IN_KEY' => $single->getLoggedInKey(),
            'NONCE_KEY' => $single->getNonceKey(),
            'AUTH_SALT' => $single->getAuthSalt(),
            'SECURE_AUTH_SALT' => $single->getSecureAuthSalt(),
            'LOGGED_IN_SALT' => $single->getLoggedInSalt(),
            'NONCE_SALT' => $single->getNonceSalt(),
            'WP_DEBUG' => false,
            'ABSPATH' => $wpRootDir
        ];
        $this->assertCount(count($expected), $constants);
        foreach ($expected as $key => $expectedValue) {
            $this->assertArrayHasKey($key, $constants);
        }
    }

    /**
     * It should allow getting the site globals
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_allow_getting_the_site_globals(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $this->fastScaffold($wpRootDir, '6.1.1')->configure($db)->install(
            'https://wp.local',
            'admin',
            'password',
            'admin@wp.local',
            'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');
        $globals = $single->getGlobals();

        $expected = [
            'table_prefix' => 'test_',
        ];
        $this->assertCount(count($expected), $globals);
        foreach ($expected as $key => $expectedValue) {
            $this->assertArrayHasKey($key, $globals);
        }
    }

    /**
     * It should allow working with options
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_allow_working_with_options(): void
    {
        $wpRootDir = FS::tmpDir('single_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost, 'test_');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db, InstallationStateInterface::SINGLE_SITE)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test');

        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals(1, $single->updateOption('foo', 'bar'));
        $this->assertEquals('bar', $db->getOption('foo'));
    }

    /**
     * It should throw if trying to execute non static Closure in WordPress
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_throw_if_trying_to_execute_non_static_closure_in_word_press(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );
        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->expectException(InvalidArgumentException::class);

        $this->assertEquals('https://wp.local',
            $single->executeClosureInWordPress(function () {
                return get_option('siteurl');
            }));
    }

    /**
     * It should allow executing a Closure in WordPress
     *
     * @test
     * @group slow
     * @group requires-mysql-server
     */
    public function should_allow_executing_a_closure_in_word_press(): void
    {
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        $wpRootDir = FS::tmpDir('single_');
        $this->fastScaffold($wpRootDir, '6.1.1')
            ->configure($db)
            ->install(
                'https://wp.local',
                'admin',
                'password',
                'admin@wp.local',
                'Test'
            );
        $single = new Single($wpRootDir, $wpRootDir . '/wp-config.php');

        $this->assertEquals('https://wp.local',
            $single->executeClosureInWordPress(static function () {
                return get_option('siteurl');
            }));
    }
}
