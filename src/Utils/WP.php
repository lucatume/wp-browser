<?php

declare(strict_types=1);

/**
 * Functions dedicate to WordPress interaction.
 *
 * @package lucatume\WPBrowser
 */

namespace lucatume\WPBrowser\Utils;

use Hautelook\Phpass\PasswordHash;
use RuntimeException;
use wpdb;

class WP
{
    /**
     * Drops the WordPress installation tables registered on the global `$wpdb` instance.
     *
     * @param wpdb               $wpdb   The global WordPress database handler instance.
     * @param array<string>|null $tables An optional white-list of tables to empty, if `null` all tables will be
     *                                   dropped.
     *
     * @return array<string> The list of dropped tables.
     */
    public static function dropWpTables(wpdb $wpdb, array $tables = null): array
    {
        $allTables = $wpdb->tables('all');
        $tablesList = $tables !== null ? array_intersect($allTables, $tables) : $allTables;
        $droppedTables = [];

        if ($wpdb->query('SET FOREIGN_KEY_CHECKS = 0') === false) {
            throw new RuntimeException('Could not disable foreign key checks.');
        }

        foreach ($tablesList as $prefixedTable) {
            if ($wpdb->query("DROP TABLE IF EXISTS {$prefixedTable}") !== true) {
                throw new RuntimeException("Could not DROP table {$prefixedTable}: " . $wpdb->last_error);
            }

            $droppedTables[] = $prefixedTable;
        }

        if ($wpdb->query('SET FOREIGN_KEY_CHECKS = 1') === false) {
            throw new RuntimeException('Could not re-enable foreign key checks.');
        }

        return $droppedTables;
    }

    /**
     * Empties the WordPress installation tables registered on the global `$wpdb` instance.
     *
     * @param wpdb               $wpdb   The global WordPress database handler instance.
     * @param array<string>|null $tables An optional white-list of tables to empty, if `null` all tables will be
     *                                   emptied.
     *
     * @return array<string> The list of emptied tables.
     */
    public static function emptyWpTables(wpdb $wpdb, array $tables = null): array
    {
        $allTables = $wpdb->tables('all');
        $tablesList = $tables !== null ? array_intersect($allTables, $tables) : $allTables;
        $emptiedTables = [];

        if ($wpdb->query('SET FOREIGN_KEY_CHECKS = 0') === false) {
            throw new RuntimeException('Could not disable foreign key checks.');
        }

        foreach ($tablesList as $prefixedTable) {
            if (!((int)$wpdb->query("SHOW TABLES LIKE '{$prefixedTable}'"))) {
                continue;
            }

            if ($wpdb->query("TRUNCATE TABLE {$prefixedTable}") === false) {
                throw new RuntimeException("Could not empty table {$prefixedTable}: " . $wpdb->last_error);
            }

            $emptiedTables[] = $prefixedTable;
        }

        if ($wpdb->query('SET FOREIGN_KEY_CHECKS = 1') === false) {
            throw new RuntimeException('Could not re-enable foreign key checks.');
        }

        return $emptiedTables;
    }

    public static function passwordHash(string $user_pass):string
    {
        return (new PasswordHash(8, true))->HashPassword($user_pass);
    }

    public static function checkHashedPassword(string $userPass, string $hashedPass): bool
    {
        return (new PasswordHash(8, true))->CheckPassword($userPass, $hashedPass);
    }
}
