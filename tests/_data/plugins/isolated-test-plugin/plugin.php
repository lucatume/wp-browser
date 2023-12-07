<?php
/**
 * Plugin Name: Isolated test plugin
 */

function isolated_test_canary_function(): string
{
    return 'Sum';
}

function isolated_test_activate(): array
{
    global $wpdb;
    $table_name   = $wpdb->prefix . 'isolated_test_table';
    $wpdb_collate = $wpdb->collate;
    $sql          =
        "CREATE TABLE {$table_name} (
         id mediumint(8) unsigned NOT NULL auto_increment ,
         data varchar(255) NULL,
         PRIMARY KEY  (id),
         KEY data (data)
         )
         COLLATE {$wpdb_collate}";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $result = dbDelta($sql);

    return $result;
}

function isolated_test_write($data): ?string
{
    global $wpdb;
    $inserted = $wpdb->insert(
        $wpdb->isolated_test,
        [ 'data' => $data ],
        [ '%s' ]
    );

    if ($inserted === false) {
        throw new RuntimeException('Could not insert ' . json_encode($data));
    }

    return $wpdb->get_var("SELECT MAX(id) AS id FROM {$wpdb->isolated_test}");
}

function isolated_test_read($id): ?string
{
    global $wpdb;

    $query = $wpdb->prepare("SELECT data FROM {$wpdb->isolated_test} WHERE id = '%d'", $id);

    return $wpdb->get_var($query);
}

/**
 * @return mixed[]|object|null
 */
function isolated_test_read_all()
{
    global $wpdb;

    return $wpdb->get_results("SELECT id, data FROM {$wpdb->isolated_test} WHERE 1=1");
}

function isolated_test_load(): void
{
    global $wpdb;
    $wpdb->isolated_test = $wpdb->prefix . 'isolated_test_table';
}

register_activation_hook(__FILE__, 'isolated_test_activate');
add_action('wp_loaded', 'isolated_test_load');
