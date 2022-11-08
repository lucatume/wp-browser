<?php

declare(strict_types=1);

/**
 * Functions dedicate to WordPress interaction.
 *
 * @package lucatume\WPBrowser
 */

namespace lucatume\WPBrowser\Utils;

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

        foreach ($tablesList as $table => $prefixedTable) {
            $dropped = $wpdb->query("DROP TABLE IF EXISTS {$prefixedTable}");

            if ($dropped !== true) {
                throw new RuntimeException("Could not DROP table {$prefixedTable}: " . $wpdb->last_error);
            }

            $droppedTables[] = $prefixedTable;
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

        foreach ($tablesList as $table => $prefixedTable) {
            $exists = (int)$wpdb->query("SHOW TABLES LIKE '{$prefixedTable}'");

            if (!$exists) {
                continue;
            }

            $deleted = $wpdb->query("TRUNCATE TABLE {$prefixedTable}");

            if ($deleted === false) {
                throw new RuntimeException("Could not empty table {$prefixedTable}: " . $wpdb->last_error);
            }

            $emptiedTables[] = $prefixedTable;
        }

        return $emptiedTables;
    }

    public static function findWpConfigFile(string $rootDir): string
    {
        $wpConfigFile = rtrim($rootDir, '\\/') . '/wp-config.php';

        if (!is_file($wpConfigFile)) {
            $wpConfigFile = dirname($rootDir) . '/wp-config.php';
        }

        return $wpConfigFile;
    }

    /**
     * @throws RuntimeException
     */
    public static function findWpConfigFileOrThrow(string $rootDir): string
    {
        $wpConfigFile = self::findWpConfigFile($rootDir);
        if (!is_file($wpConfigFile)) {
            throw new RuntimeException("wp-config.php file not found in $rootDir or in its parent directory.");
        }
        return $wpConfigFile;
    }

    /**
     * @throws RuntimeException
     */
    public static function findWpSettingsFileOrThrow(string $rootDir): string
    {
        $wpSettingsFile = rtrim($rootDir, '\\/') . '/wp-settings.php';
        if (!is_file($wpSettingsFile)) {
            throw new RuntimeException("wp-settings.php file not found in $rootDir.");
        }
        return $wpSettingsFile;
    }

    public static function findRootDirFromDirOrThrow(string $workDir): string
    {
        $dir = self::findRootDirFromDir($workDir);

        if ($dir === false) {
            throw new RuntimeException("Could not find WordPress root directory from $workDir.");
        }

        return $dir;
    }

    public static function findRootDirFromDir(string $workDir): string|false
    {
        $dir = $workDir;

        while (!(file_exists($dir . '/wp-load.php') || $dir === '/')) {
            $dir = dirname($dir);
        }

        return file_exists( $dir . '/wp-load.php') ? $dir : false;
    }

}

