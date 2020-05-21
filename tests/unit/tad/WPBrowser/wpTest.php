<?php namespace tad\WPBrowser;

use tad\WPBrowser\Traits\WithStubProphecy;

require_once codecept_root_dir('tests/_support/lib/wpdb.php');

class wpTest extends \Codeception\Test\Unit
{
    use WithStubProphecy;

    /**
     * Test dropWpTables will drop all tables if whitelist not specified
     */
    public function test_drop_wp_tables_will_drop_all_tables_if_whitelist_not_specified()
    {
        $queries = [];
        $wpdb = $this->makeEmpty(\wpdb::class, [
            'tables' => ['wp_options', 'wp_posts', 'wp_users'],
            'query' => static function ($query) use (&$queries) {
                $queries[] = $query;

                return true;
            },
        ]);

        $dropped = dropWpTables($wpdb);

        $this->assertEquals([
            'DROP TABLE wp_options IF EXISTS',
            'DROP TABLE wp_posts IF EXISTS',
            'DROP TABLE wp_users IF EXISTS'
        ], $queries);
        $this->assertEquals(['wp_options', 'wp_posts', 'wp_users'], $dropped);
    }

    /**
     * Test dropWpTables will drop only tables in whitelist
     */
    public function test_drop_wp_tables_will_drop_only_tables_in_whitelist()
    {
        $queries = [];
        $wpdb = $this->makeEmpty(\wpdb::class, [
            'tables' => ['wp_options', 'wp_posts', 'wp_users'],
            'query' => static function ($query) use (&$queries) {
                $queries[] = $query;

                return true;
            },
        ]);

        $dropped = dropWpTables($wpdb, ['wp_posts', 'wp_users', 'foo_bar']);

        $this->assertEquals([
            'DROP TABLE wp_posts IF EXISTS',
            'DROP TABLE wp_users IF EXISTS',
        ], $queries);
        $this->assertEquals(['wp_posts', 'wp_users'], $dropped);
    }

    /**
     * Test dropWpTables will throw if table cannot be dropped
     */
    public function test_drop_wp_tables_will_throw_if_table_cannot_be_dropped()
    {
        $wpdb = $this->makeEmpty(\wpdb::class, [
            'tables' => ['wp_options', 'wp_posts', 'wp_users'],
            'query' => static function ($query) use (&$queries) {
                return false;
            },
            'last_error' => 'test test test'
        ]);

        $this->expectException(\RuntimeException::class);

        $emptied = dropWpTables($wpdb);
    }

    /**
     * Test emptyWpTables will empty all tables if whitelist not specified
     */
    public function test_empty_wp_tables_will_empty_all_tables_if_whitelist_not_specified()
    {
        $queries = [];
        $wpdb = $this->makeEmpty(\wpdb::class, [
            'tables' => ['wp_options', 'wp_posts', 'wp_users'],
            'query' => static function ($query) use (&$queries) {
                $queries[] = $query;

                return true;
            },
        ]);

        $emptied = emptyWpTables($wpdb);

        $this->assertEquals([
	        "SHOW TABLES LIKE 'wp_options'",
            'TRUNCATE TABLE wp_options',
	        "SHOW TABLES LIKE 'wp_posts'",
            'TRUNCATE TABLE wp_posts',
	        "SHOW TABLES LIKE 'wp_users'",
            'TRUNCATE TABLE wp_users',
        ], $queries);
        $this->assertEquals(['wp_options', 'wp_posts', 'wp_users'], $emptied);
    }

    /**
     * Test emptyWpTables will empty only tables in whitelist
     */
    public function test_empty_wp_tables_will_empty_only_tables_in_whitelist()
    {
        $queries = [];
        $wpdb = $this->makeEmpty(\wpdb::class, [
            'tables' => ['wp_options', 'wp_posts', 'wp_users'],
            'query' => static function ($query) use (&$queries) {
                $queries[] = $query;

                return true;
            },
        ]);

        $emptied = emptyWpTables($wpdb, ['wp_posts', 'wp_users', 'foo_bar']);

        $this->assertEquals([
            "SHOW TABLES LIKE 'wp_posts'",
            'TRUNCATE TABLE wp_posts',
	        "SHOW TABLES LIKE 'wp_users'",
	        'TRUNCATE TABLE wp_users',
        ], $queries);
        $this->assertEquals(['wp_posts', 'wp_users'], $emptied);
    }
}
