<?php

namespace tad\WPBrowser\Module\Support;

use Dotenv\Environment\DotenvFactory;
use Dotenv\Loader;
use Prophecy\Argument;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use tad\Test\Constants as TestConstants;
use tad\WPBrowser\Environment\Constants;
use tad\WPBrowser\Generators\Tables;

require_once codecept_root_dir('tests/_support/lib/wpdb.php');

class WPHealthcheckTest extends \Codeception\Test\Unit
{
    use SnapshotAssertions;

    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $constants;
    protected $database;
    protected $directories;

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $sut = $this->make_instance();

        $this->assertInstanceOf(WPHealthcheck::class, $sut);
    }

    /**
     * @return WPHealthcheck
     */
    private function make_instance()
    {
        if (null === $this->constants) {
            $this->constants = new Constants();
        }
        if (null === $this->database) {
            $this->database = new WordPressDatabase($this->constants);
        }
        if (null === $this->directories) {
            $this->directories = new WordPressDirectories($this->constants);
        }
        $instance = new WPHealthcheck($this->constants, $this->database, $this->directories);
        $instance->useRelativePaths(true);
        return $instance;
    }

    /**
     * Test report with empty constants
     */
    public function test_report_with_empty_constants()
    {
        $this->constants = new TestConstants();
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with set ABSPATH
     */
    public function test_with_set_ABSPATH()
    {
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/')
        ]);
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with working installation
     */
    public function test_with_working_installation()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER')
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with working multisite installation
     */
    public function test_with_working_multisite_installation()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('MU_SUBDIR_WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('MU_SUBDIR_WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('MU_SUBDIR_DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER'),
            'MULTISITE' => true,
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with working subdomain installation
     */
    public function test_with_working_subdomain_installation()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('MU_SUBDIR_WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('MU_SUBDIR_WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('MU_SUBDIR_DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER'),
            'SUBDOMAIN_INSTALL' => true,
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with broken PDO connection
     */
    public function test_with_broken_pdo_connection()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER')
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $database = $this->prophesize(WordPressDatabase::class);
        $database->query(Argument::cetera())->willReturn(false);
        $database->getTablePrefix(Argument::type('string'))
            ->will(function (array $args) {
                return $args[0];
            });
        $database->checkDbConnection()->willReturn(false);
        $database->getDbConnectionError()->willReturn('Cannot connect.');
        $this->database = $database->reveal();
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with no tables in db
     */
    public function test_with_no_tables_in_db()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('EMPTY_DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER')
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with no tables for prefix
     */
    public function test_with_no_tables_for_prefix()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER')
        ]);
        $GLOBALS['table_prefix'] = 'not_existing_';
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with available wpdb global
     */
    public function test_with_available_wpdb_global()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER')
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $GLOBALS['wpdb'] = new \wpdb();
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with diff. set of tables specified from wpdb global
     */
    public function test_with_diff_set_of_tables_specified_from_wpdb_global()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER')
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $wpdb = new \wpdb();
        $wpdb->tables = ['wp_foo', 'wp_bar'];
        $GLOBALS['wpdb'] = $wpdb;
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with mu-plugins dir constant set
     */
    public function test_with_mu_plugins_dir_constant_set()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER'),
            'WPMU_PLUGIN_DIR' => codecept_root_dir('vendor/wordpress/wordpress/wp-content/plugins')
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with empty mu-plugins directory
     */
    public function test_with_empty_mu_plugins_directory()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER'),
            'WP_PLUGIN_DIR' => codecept_data_dir('empty'),
            'WPMU_PLUGIN_DIR' => codecept_data_dir('empty')
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with empty plugins folder
     */
    public function test_with_empty_plugins_folder()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER'),
            'WP_PLUGIN_DIR' => codecept_data_dir('empty')
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with empty and missing template and stylesheet files
     */
    public function test_with_empty_and_missing_template_and_stylesheet_files()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER'),
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $database = $this->prophesize(WordPressDatabase::class);
        $database->getTablePrefix(Argument::type('string'))->willReturn('wp_');
        $database->checkDbConnection()->willReturn(true);
        $database->query('SHOW TABLES')->willReturn(Tables::blogTables('wp_'));
        $database->getOption('siteurl', false)->willReturn('http://wp.localhost');
        $database->getOption('template', false)->willReturn('foo-bar');
        $database->getOption('stylesheet', false)->willReturn(false);
        $database->getOption('active_plugins', false)->willReturn(serialize([
            'one/one.php',
            'two/two.php'
        ]));
        $this->database = $database->reveal();
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with inactive plugins
     */
    public function test_with_inactive_plugins()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER'),
            'WP_PLUGIN_DIR' => codecept_data_dir('plugins')
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $database = $this->prophesize(WordPressDatabase::class);
        $database->getTablePrefix(Argument::type('string'))->willReturn('wp_');
        $database->checkDbConnection()->willReturn(true);
        $database->query('SHOW TABLES')->willReturn(Tables::blogTables('wp_'));
        $database->getOption('siteurl', false)->willReturn('http://wp.localhost');
        $database->getOption('template', false)->willReturn('twentynineteen');
        $database->getOption('stylesheet', false)->willReturn('twentynineteen');
        $database->getOption('active_plugins', false)->willReturn(serialize([]));
        $this->database = $database->reveal();
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with missing siteurl option
     */
    public function test_with_missing_siteurl_option()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER'),
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $database = $this->prophesize(WordPressDatabase::class);
        $database->getTablePrefix(Argument::type('string'))->willReturn('wp_');
        $database->checkDbConnection()->willReturn(true);
        $database->query('SHOW TABLES')->willReturn(Tables::blogTables('wp_'));
        $database->getOption('siteurl', false)->willReturn('');
        $database->getOption('template', false)->willReturn('twentynineteen');
        $database->getOption('stylesheet', false)->willReturn('twentynineteen');
        $database->getOption('active_plugins', false)->willReturn(serialize([]));
        $database->getTable('options')->willReturn('wp_options');
        $this->database = $database->reveal();
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test multisite with missing blogs entry for site
     */
    public function test_multisite_with_missing_blogs_entry_for_site()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER'),
            'MULTISITE' => true,
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $database = $this->prophesize(WordPressDatabase::class);
        $database->getTablePrefix(Argument::type('string'))->willReturn('wp_');
        $database->checkDbConnection()->willReturn(true);
        $database->query('SHOW TABLES')->willReturn(Tables::blogTables('wp_'));
        $database->getOption('siteurl', false)->willReturn('http://wp.localhost');
        $database->query(Argument::containingString('FROM wp_blogs'))->willReturn(false);
        $database->getOption('template', false)->willReturn('twentynineteen');
        $database->getOption('stylesheet', false)->willReturn('twentynineteen');
        $database->getOption('active_plugins', false)->willReturn(serialize([]));
        $database->getTable('blogs')->willReturn('wp_blogs');
        $this->database = $database->reveal();
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with no results in blogs for blog domain
     */
    public function test_with_no_results_in_blogs_for_blog_domain()
    {
        $env = new Loader([codecept_root_dir('.env.testing')], new DotenvFactory());
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir('vendor/wordpress/wordpress/'),
            'WP_HOME' => $env->getEnvironmentVariable('WP_URL'),
            'WP_SITEURL' => $env->getEnvironmentVariable('WP_URL'),
            'DB_HOST' => $env->getEnvironmentVariable('DB_HOST'),
            'DB_NAME' => $env->getEnvironmentVariable('DB_NAME'),
            'DB_PASSWORD' => $env->getEnvironmentVariable('DB_PASSWORD'),
            'DB_USER' => $env->getEnvironmentVariable('DB_USER'),
            'MULTISITE' => true,
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $database = $this->prophesize(WordPressDatabase::class);
        $database->getTablePrefix(Argument::type('string'))->willReturn('wp_');
        $database->checkDbConnection()->willReturn(true);
        $database->query('SHOW TABLES')->willReturn(Tables::blogTables('wp_'));
        $database->getOption('siteurl', false)->willReturn('http://foo.bar');
        $realDb = new WordPressDatabase($this->constants);
        $realDb->checkDbConnection();
        $statement = $realDb->query('SELECT * FROM wp_blogs WHERE 1 = 0');
        $database->query(Argument::containingString('FROM wp_blogs'))->willReturn($statement);
        $database->getOption('template', false)->willReturn('twentynineteen');
        $database->getOption('stylesheet', false)->willReturn('twentynineteen');
        $database->getOption('active_plugins', false)->willReturn(serialize([]));
        $database->getTable('blogs')->willReturn('wp_blogs');
        $this->database = $database->reveal();
        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
