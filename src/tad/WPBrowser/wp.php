<?php
/**
 * Function dedicate to WordPress interaction.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

function dropWpTables(\wpdb $wpdb, array $tables = null)
{
    $allTables = $wpdb->tables('all');

    foreach ($allTables as $table => $prefixedTable) {
        if (!in_array($prefixedTable, $tables, true)) {
            continue;
        }
        $dropped = $wpdb->query("DROP TABLE {$prefixedTable} IF EXISTS ");

        if ($dropped!== true) {
            throw new \RuntimeException("Could not DROP table {$prefixedTable}: " . $wpdb->last_error);
        }
    }
}

function emptyWpTables(\wpdb $wpdb, array $tables = null)
{
    // Make sure we start from empty tables.
    $dropList = $wpdb->get_col("show tables like '{$wpdb->prefix}%'");
    if ($tables !== null) {
        $dropList = array_intersect($dropList, $tables);
    }

    if (! empty($dropList)) {
        $allTables = $wpdb->tables('all');
        foreach ($allTables as $table => $prefixedTable) {
            if (! in_array($prefixedTable, $dropList, true)) {
                continue;
            }
            $deleted = $wpdb->query("DELETE FROM {$prefixedTable} WHERE 1=1");

            if ($deleted === false) {
                throw new \RuntimeException("Could not empty table {$prefixedTable}: " . $wpdb->last_error);
            }
        }
    }
}
