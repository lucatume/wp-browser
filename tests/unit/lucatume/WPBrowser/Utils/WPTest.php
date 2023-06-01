<?php namespace lucatume\WPBrowser;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\Utils\WP;
use PDO;
use RuntimeException;
use wpdb;

class wpTest extends Unit
{
    private function makeMockWpdbWithTables(array $tables): array
    {
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $dbName = Random::dbName();
        $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPassword);
        if ($pdo->exec("CREATE DATABASE $dbName") === false) {
            throw new RuntimeException("Could not create database $dbName");
        }
        if ($pdo->exec("USE $dbName") === false) {
            throw new RuntimeException("Could not use database $dbName");
        }
        foreach ($tables as $table) {
            if ($pdo->exec("CREATE TABLE $table (id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id))") === false) {
                throw new RuntimeException("Could not create table $table");
            }
        }
        $wpdb = $this->make(wpdb::class, [
            'tables' => $tables,
            'query' => static function (string $query) use (&$wpdb, $pdo) {
                $result = !($pdo->query($query)->fetchAll() === false);
                $wpdb->last_error = $pdo->errorInfo()[2];
                return $result;
            },
        ]);

        return [$wpdb, $pdo];
    }

    /**
     * Test dropWpTables will drop all tables if whitelist not specified
     */
    public function test_drop_wp_tables_will_drop_all_tables_if_whitelist_not_specified(): void
    {
        [$wpdb, $pdo] = $this->makeMockWpdbWithTables([
            'wp_options',
            'wp_posts',
            'wp_users',
            'wp_custom_table_1',
            'wp_custom_table_2'
        ]);

        $dropped = WP::dropWpTables($wpdb);

        $this->assertEquals(['wp_options', 'wp_posts', 'wp_users', 'wp_custom_table_1', 'wp_custom_table_2'], $dropped);
        $this->assertFalse($pdo->query("SHOW TABLES LIKE 'wp_options'")->fetchColumn());
        $this->assertFalse($pdo->query("SHOW TABLES LIKE 'wp_posts'")->fetchColumn());
        $this->assertFalse($pdo->query("SHOW TABLES LIKE 'wp_users'")->fetchColumn());
        $this->assertFalse($pdo->query("SHOW TABLES LIKE 'wp_custom_table_1'")->fetchColumn());
        $this->assertFalse($pdo->query("SHOW TABLES LIKE 'wp_custom_table_2'")->fetchColumn());
    }

    /**
     * Test dropWpTables will drop only tables in whitelist
     */
    public function test_drop_wp_tables_will_drop_only_tables_in_whitelist(): void
    {
        [$wpdb, $pdo] = $this->makeMockWpdbWithTables([
            'wp_options',
            'wp_posts',
            'wp_users',
            'wp_custom_table_1',
            'wp_custom_table_2'
        ]);

        $dropped = WP::dropWpTables($wpdb, ['wp_posts', 'wp_users', 'wp_custom_table_1', 'wp_custom_table_3']);

        $this->assertEquals(['wp_posts', 'wp_users', 'wp_custom_table_1'], $dropped);
        $this->assertEquals('wp_options', $pdo->query("SHOW TABLES LIKE 'wp_options'")->fetchColumn());
        $this->assertFalse($pdo->query("SHOW TABLES LIKE 'wp_posts'")->fetchColumn());
        $this->assertFalse($pdo->query("SHOW TABLES LIKE 'wp_users'")->fetchColumn());
        $this->assertFalse($pdo->query("SHOW TABLES LIKE 'wp_custom_table_1'")->fetchColumn());
        $this->assertEquals('wp_custom_table_2', $pdo->query("SHOW TABLES LIKE 'wp_custom_table_2'")->fetchColumn());
    }

    public function test_foreign_key_checks_will_not_prevent_table_dropping(): void
    {
        [$wpdb, $pdo] = $this->makeMockWpdbWithTables([
            'wp_options',
            'wp_posts',
            'wp_users',
            'wp_custom_table_1',
            'wp_custom_table_2'
        ]);
        // Make wp_custom_table_1 have a foreign key of wp_custom_table_2.
        $pdo->exec("ALTER TABLE wp_custom_table_2 ADD CONSTRAINT fk_wp_custom_table_2_wp_custom_table_1 FOREIGN KEY (id) REFERENCES wp_custom_table_1(id)");

        $dropped = WP::dropWpTables($wpdb, ['wp_posts', 'wp_users', 'wp_custom_table_1', 'wp_custom_table_3']);

        $this->assertEquals(['wp_posts', 'wp_users', 'wp_custom_table_1'], $dropped);
        $this->assertEquals('wp_options', $pdo->query("SHOW TABLES LIKE 'wp_options'")->fetchColumn());
        $this->assertFalse($pdo->query("SHOW TABLES LIKE 'wp_posts'")->fetchColumn());
        $this->assertFalse($pdo->query("SHOW TABLES LIKE 'wp_users'")->fetchColumn());
        $this->assertFalse($pdo->query("SHOW TABLES LIKE 'wp_custom_table_1'")->fetchColumn());
        $this->assertEquals('wp_custom_table_2', $pdo->query("SHOW TABLES LIKE 'wp_custom_table_2'")->fetchColumn());
    }

    public function test_throws_if_foreign_key_checks_cannot_be_disabled_enabled_while_dropping_tables(): void
    {
        $wpdb = $this->make(wpdb::class, [
            'query' => static function (string $query) {
                return false;
            },
        ]);

        $this->expectException(RuntimeException::class);

        WP::dropWpTables($wpdb);

        $wpdb = $this->make(wpdb::class, [
            'query' => static function (string $query) {
                return $query !== 'SET FOREIGN_KEY_CHECKS = 1';
            }
        ]);

        $this->expectException(RuntimeException::class);

        WP::dropWpTables($wpdb);
    }

    /**
     * Test dropWpTables will throw if table cannot be dropped
     */
    public function test_drop_wp_tables_will_throw_if_table_cannot_be_dropped(): void
    {
        $wpdb = $this->make(wpdb::class, [
            'tables' => [
                'wp_options',
                'wp_posts',
                'wp_users',
                'wp_custom_table_1',
                'wp_custom_table_2'
            ],
            'query' => static function (string $query) use (&$wpdb) {
                // Fail when dropping the wp_custom_table_1 table.
                if ($query === 'DROP TABLE IF EXISTS wp_custom_table_1') {
                    $wpdb->last_error = 'Error dropping table wp_custom_table_1';
                    return false;
                }
                return true;
            }
        ]);

        $this->expectException(RuntimeException::class);

        WP::dropWpTables($wpdb, ['wp_posts', 'wp_users', 'wp_custom_table_1', 'wp_custom_table_3']);
    }

    public function throws_if_foreign_key_checks_cannot_be_disabled_enabled_while_emptying_tables(): void
    {
        $wpdb = $this->make(wpdb::class, [
            'query' => static function (string $query) {
                return false;
            },
        ]);

        $this->expectException(RuntimeException::class);

        WP::emptyWpTables($wpdb);

        $wpdb = $this->make(wpdb::class, [
            'query' => static function (string $query) {
                return $query !== 'SET FOREIGN_KEY_CHECKS = 1';
            }
        ]);

        $this->expectException(RuntimeException::class);

        WP::emptyWpTables($wpdb);
    }

    /**
     * Test emptyWpTables will empty all tables if whitelist not specified
     */
    public function test_empty_wp_tables_will_empty_all_tables_if_whitelist_not_specified(): void
    {
        $tables = [
            'wp_options',
            'wp_posts',
            'wp_users',
            'wp_custom_table_1',
            'wp_custom_table_2'
        ];
        [$wpdb, $pdo] = $this->makeMockWpdbWithTables($tables);
        // Make wp_custom_table_1 have a foreign key of wp_custom_table_2.
        $pdo->exec("ALTER TABLE wp_custom_table_2 ADD CONSTRAINT fk_wp_custom_table_2_wp_custom_table_1 FOREIGN KEY (id) REFERENCES wp_custom_table_1(id)");
        // Insert some data into the tables.
        foreach ($tables as $table) {
            $pdo->exec("INSERT INTO $table SET id=1");
            $pdo->exec("INSERT INTO $table SET id=2");
            $pdo->exec("INSERT INTO $table SET id=3");
        }

        $emptied = WP::emptyWpTables($wpdb);

        $this->assertEquals($tables, $emptied);
        foreach ($tables as $table) {
            // Assert the table exists.
            $this->assertEquals($table, $pdo->query("SHOW TABLES LIKE '$table'")->fetchColumn());
            // Assert the table is empty.
            $this->assertEquals(0, $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn());
        }
    }

    /**
     * Test emptyWpTables will empty only tables in whitelist
     */
    public function test_empty_wp_tables_will_empty_only_tables_in_whitelist(): void
    {
        $tables = [
            'wp_options',
            'wp_posts',
            'wp_users',
            'wp_custom_table_1',
            'wp_custom_table_2'
        ];
        [$wpdb, $pdo] = $this->makeMockWpdbWithTables($tables);
        // Make wp_custom_table_1 have a foreign key of wp_custom_table_2.
        $pdo->exec("ALTER TABLE wp_custom_table_2 ADD CONSTRAINT fk_wp_custom_table_2_wp_custom_table_1 FOREIGN KEY (id) REFERENCES wp_custom_table_1(id)");
        // Insert some data into the tables.
        foreach ($tables as $table) {
            $pdo->exec("INSERT INTO $table SET id=1");
            $pdo->exec("INSERT INTO $table SET id=2");
            $pdo->exec("INSERT INTO $table SET id=3");
        }

        $emptied = WP::emptyWpTables($wpdb, ['wp_posts', 'wp_users', 'wp_custom_table_2', 'wp_custom_table_3']);

        $this->assertEquals($emptied, ['wp_posts', 'wp_users', 'wp_custom_table_2']);
        $this->assertEquals(3, $pdo->query("SELECT COUNT(*) FROM wp_options")->fetchColumn());
        $this->assertEquals(0, $pdo->query("SELECT COUNT(*) FROM wp_posts")->fetchColumn());
        $this->assertEquals(0, $pdo->query("SELECT COUNT(*) FROM wp_users")->fetchColumn());
        $this->assertEquals(3, $pdo->query("SELECT COUNT(*) FROM wp_custom_table_1")->fetchColumn());
        $this->assertEquals(0, $pdo->query("SELECT COUNT(*) FROM wp_custom_table_2")->fetchColumn());
    }
}
