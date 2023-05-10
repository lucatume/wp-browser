<?php

namespace lucatume\WPBrowser\WordPress;

use PHPUnit\Framework\Assert as PHPUnitAssert;

class Assert
{

    public static function assertTableExists(string $tableName): void
    {
        global $wpdb;
        $table = $wpdb->prefix . $tableName;
        $tableExists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
        PHPUnitAssert::assertTrue($tableExists, "Table {$table} does not exist");
    }

    public static function assertUpdatesDisabled(): void
    {
        PHPUnitAssert::assertEqualsWithDelta(time(), \get_site_transient('update_core')->last_checked, 10);
        PHPUnitAssert::assertEqualsWithDelta(time(), \get_site_transient('update_plugins')->last_checked, 10);
        PHPUnitAssert::assertEqualsWithDelta(time(), \get_site_transient('update_themes')->last_checked, 10);
        foreach ([
                     ['admin_init', '_maybe_update_core'],
                     ['admin_init', '_maybe_update_plugins'],
                     ['admin_init', '_maybe_update_themes'],
                     ['admin_init', 'default_password_nag_handler'],
                 ] as [$action, $callback]) {
            PHPUnitAssert::assertFalse(\has_action($action, $callback));
        }
    }
}
