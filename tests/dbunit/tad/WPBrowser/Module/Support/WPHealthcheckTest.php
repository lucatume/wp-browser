<?php

namespace tad\WPBrowser\Module\Support;

use Prophecy\Argument;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use tad\Test\Constants as TestConstants;
use tad\WPBrowser\Environment\Constants;
use tad\WPBrowser\Generators\Tables;
use function tad\WPBrowser\Tests\Support\env;

require_once codecept_root_dir('tests/_support/lib/wpdb.php');

class WPHealthcheckTest extends \Codeception\Test\Unit
{
    use SnapshotAssertions;

    /**
     * @var \UnitTester
     */
    protected $tester;
    protected $env;
    protected $constants;
    protected $database;
    protected $directories;

    public function _before()
    {
        parent::_before();
        $this->env = env();
    }

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

        $run = $sut->run();
        codecept_debug($run);
        $this->assertMatchesJsonSnapshot(json_encode($run, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with set ABSPATH
     */
    public function test_with_set_ABSPATH()
    {
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/')
        ]);
        $sut = $this->make_instance();

        $run = $sut->run();
        codecept_debug($run);
        $this->assertMatchesJsonSnapshot(json_encode($run, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function env($name)
    {
        return call_user_func($this->env, $name);
    }

    /**
     * Test with working installation
     */
    public function test_with_working_installation()
    {
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER')
        ]);
        $GLOBALS['table_prefix'] = $this->env('WORDPRESS_TABLE_PREFIX');
        $sut = $this->make_instance();

        $run = $sut->run();
        codecept_debug($run);
        $this->assertMatchesJsonSnapshot(json_encode($run, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with working multisite subdir installation
     */
    public function test_with_working_multisite_subdir_installation()
    {
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_SUBDIR_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_SUBDIR_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_SUBDIR_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER'),
            'MULTISITE' => true,
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $sut = $this->make_instance();

        $run = $sut->run();
        codecept_debug($run);
        $this->assertMatchesJsonSnapshot(json_encode($run, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with working multisite subdomain installation
     */
    public function test_with_working_multisite_subdomain_installation()
    {
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_SUBDOMAIN_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_SUBDOMAIN_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_SUBDOMAIN_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER'),
            'SUBDOMAIN_INSTALL' => true,
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $sut = $this->make_instance();

        $run = $sut->run();
        codecept_debug($run);
        $this->assertMatchesJsonSnapshot(json_encode($run, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with broken PDO connection
     */
    public function test_with_broken_pdo_connection()
    {
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER')
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $database = $this->prophesize(WordPressDatabase::class);
        $database->query(Argument::cetera())->willReturn(false);
        $database->getTablePrefix(Argument::type('string'))
            ->will(function (array $args) {
                return $args[0];
            });
        $database->checkDbConnection(Argument::cetera())->willReturn(false);
        $database->getDbConnectionError()->willReturn('Cannot connect.');
        $this->database = $database->reveal();
        $sut = $this->make_instance();

        $run = $sut->run();
        codecept_debug($run);
        $this->assertMatchesJsonSnapshot(json_encode($run, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with no tables in db
     */
    public function test_with_no_tables_in_db()
    {
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_WP_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_WP_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_EMPTY_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER')
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $sut = $this->make_instance();

        $run = $sut->run();
        codecept_debug($run);
        $this->assertMatchesJsonSnapshot(json_encode($run, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with no tables for prefix
     */
    public function test_with_no_tables_for_prefix()
    {
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER')
        ]);
        $GLOBALS['table_prefix'] = 'not_existing_';
        $sut = $this->make_instance();

        $run = $sut->run();
        codecept_debug($run);
        $this->assertMatchesJsonSnapshot(json_encode($run, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test with available wpdb global
     */
    public function test_with_available_wpdb_global()
    {
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER')
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
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER')
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
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER'),
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
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER'),
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
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER'),
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
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER'),
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $database = $this->prophesize(WordPressDatabase::class);
        $database->getTablePrefix(Argument::type('string'))->willReturn('wp_');
        $database->checkDbConnection(Argument::cetera())->willReturn(true);
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
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER'),
            'WP_PLUGIN_DIR' => codecept_data_dir('plugins')
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $database = $this->prophesize(WordPressDatabase::class);
        $database->getTablePrefix(Argument::type('string'))->willReturn('wp_');
        $database->checkDbConnection(Argument::cetera())->willReturn(true);
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
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER'),
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $database = $this->prophesize(WordPressDatabase::class);
        $database->getTablePrefix(Argument::type('string'))->willReturn('wp_');
        $database->checkDbConnection(Argument::cetera())->willReturn(true);
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
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER'),
            'MULTISITE' => true,
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $database = $this->prophesize(WordPressDatabase::class);
        $database->getTablePrefix(Argument::type('string'))->willReturn('wp_');
        $database->checkDbConnection(Argument::cetera())->willReturn(true);
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
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER'),
            'MULTISITE' => true,
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $database = $this->prophesize(WordPressDatabase::class);
        $database->getTablePrefix(Argument::type('string'))->willReturn('wp_');
        $database->checkDbConnection(Argument::cetera())->willReturn(true);
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

    /**
     * Test w globally set theme template
     */
    public function test_w_globally_set_theme_template()
    {
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER'),
            'MULTISITE' => true,
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $database = $this->prophesize(WordPressDatabase::class);
        $database->getTablePrefix(Argument::type('string'))->willReturn('wp_');
        $database->checkDbConnection(Argument::cetera())->willReturn(true);
        $database->query('SHOW TABLES')->willReturn(Tables::blogTables('wp_'));
        $database->getOption('siteurl', false)->willReturn('http://foo.bar');
        $realDb = new WordPressDatabase($this->constants);
        $realDb->checkDbConnection();
        $statement = $realDb->query('SELECT * FROM wp_blogs WHERE 1 = 0');
        $database->query(Argument::containingString('FROM wp_blogs'))->willReturn($statement);

        // Set the template and stylesheet as WPLoader would do.
        $GLOBALS['wp_tests_options']['template'] = 'dummy';
        $GLOBALS['wp_tests_options']['stylesheet'] = 'dummy';

        $this->directories = $this->make(WordPressDirectories::class, [
            'constants' => new Constants(),
            'getThemesDir' => codecept_data_dir('themes')
        ]);

        $database->getOption('template', false)->willReturn('twentynineteen');
        $database->getOption('stylesheet', false)->willReturn('twentynineteen');
        $database->getOption('active_plugins', false)->willReturn(serialize([]));
        $database->getTable('blogs')->willReturn('wp_blogs');
        $this->database = $database->reveal();

        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Test w globally set theme template and stylesheet
     */
    public function test_w_globally_set_theme_template_and_stylesheet()
    {
        $this->constants = new TestConstants([
            'ABSPATH' => codecept_root_dir($this->env('WORDPRESS_ROOT_DIR') . '/'),
            'WP_HOME' => $this->env('WORDPRESS_URL'),
            'WP_SITEURL' => $this->env('WORDPRESS_URL'),
            'DB_HOST' => $this->env('WORDPRESS_DB_HOST'),
            'DB_NAME' => $this->env('WORDPRESS_DB_NAME'),
            'DB_PASSWORD' => $this->env('WORDPRESS_DB_PASSWORD'),
            'DB_USER' => $this->env('WORDPRESS_DB_USER'),
            'MULTISITE' => true,
        ]);
        $GLOBALS['table_prefix'] = 'wp_';
        $database = $this->prophesize(WordPressDatabase::class);
        $database->getTablePrefix(Argument::type('string'))->willReturn('wp_');
        $database->checkDbConnection(Argument::cetera())->willReturn(true);
        $database->query('SHOW TABLES')->willReturn(Tables::blogTables('wp_'));
        $database->getOption('siteurl', false)->willReturn('http://foo.bar');
        $realDb = new WordPressDatabase($this->constants);
        $realDb->checkDbConnection();
        $statement = $realDb->query('SELECT * FROM wp_blogs WHERE 1 = 0');
        $database->query(Argument::containingString('FROM wp_blogs'))->willReturn($statement);

        // Set the template and stylesheet as WPLoader would do.
        $GLOBALS['wp_tests_options']['template'] = 'dummy';
        $GLOBALS['wp_tests_options']['stylesheet'] = 'test-child-theme';

        $this->directories = $this->make(WordPressDirectories::class, [
            'constants' => new Constants(),
            'getThemesDir' => codecept_data_dir('themes')
        ]);

        $database->getOption('template', false)->willReturn('twentynineteen');
        $database->getOption('stylesheet', false)->willReturn('twentynineteen');
        $database->getOption('active_plugins', false)->willReturn(serialize([]));
        $database->getTable('blogs')->willReturn('wp_blogs');
        $this->database = $database->reveal();

        $sut = $this->make_instance();

        $this->assertMatchesJsonSnapshot(json_encode($sut->run(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
