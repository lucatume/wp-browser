<?php
/**
 * Functions dedicate to WordPress interaction.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

/**
 * Drops the WordPress installation tables registered on the global `$wpdb` instance.
 *
 * @param \wpdb $wpdb The global WordPress database handler instance.
 * @param array<string>|null $tables An optional white-list of tables to empty, if `null` all tables will be dropped.
 *
 * @return array<string> The list of dropped tables.
 */
function dropWpTables(\wpdb $wpdb, array $tables = null)
{
    $allTables = $wpdb->tables('all');
    $tablesList = $tables !== null ? array_intersect($allTables, $tables) : $allTables;
    $droppedTables = [];

    foreach ($tablesList as $table => $prefixedTable) {
        $dropped = $wpdb->query("DROP TABLE {$prefixedTable} IF EXISTS");

        if ($dropped !== true) {
            throw new \RuntimeException("Could not DROP table {$prefixedTable}: " . $wpdb->last_error);
        }

        $droppedTables[] = $prefixedTable;
    }

    return $droppedTables;
}

/**
 * Empties the WordPress installation tables registered on the global `$wpdb` instance.
 *
 * @param \wpdb $wpdb The global WordPress database handler instance.
 * @param array<string>|null $tables An optional white-list of tables to empty, if `null` all tables will be emptied.
 *
 * @return array<string> The list of emptied tables.
 */
function emptyWpTables(\wpdb $wpdb, array $tables = null)
{
    $allTables = $wpdb->tables('all');
    $tablesList = $tables !== null ? array_intersect($allTables, $tables) : $allTables;
    $emptiedTables = [];

    foreach ($tablesList as $table => $prefixedTable) {
        $exists = (int)$wpdb->query("SHOW TABLES LIKE '{$prefixedTable}'");

        if (!$exists) {
            continue;
        }

        $deleted = $wpdb->query("TRUNCATE TABLE {$prefixedTable}");

        if ($deleted === false) {
            throw new \RuntimeException("Could not empty table {$prefixedTable}: " . $wpdb->last_error);
        }

        $emptiedTables[] = $prefixedTable;
    }

    return $emptiedTables;
}
