<?php

namespace lucatume\WPBrowser\WordPress;

use PHPUnit\Framework\Assert as PHPUnitAssert;

class Assert
{

    public static function assertTableExists(string $tableName)
    {
        global $wpdb;
        $table = $wpdb->prefix . $tableName;
        $tableExists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
        PHPUnitAssert::assertTrue($tableExists, "Table {$table} does not exist");
    }
}
