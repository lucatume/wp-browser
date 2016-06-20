<?php
/**
 * Installs WordPress for the purpose of the unit-tests
 *
 */
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

$configuration = unserialize($argv[1]);

if (!empty($configuration['activePlugins'])) {
    $activePlugins = unserialize($configuration['activePlugins']);
} else {
    $activePlugins = [];
}

foreach ($configuration['constants'] as $key => $value) {
    define($key, $value);
}

$table_prefix = WP_TESTS_TABLE_PREFIX;

$multisite = !empty($argv[2]) ? $argv[2] : false;

define('WP_INSTALLING', true);
//require_once $config_file_path;
require_once dirname(__FILE__) . '/functions.php';

tests_reset__SERVER();

$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

require_once ABSPATH . '/wp-settings.php';

require_once ABSPATH . '/wp-admin/includes/upgrade.php';
require_once ABSPATH . '/wp-includes/wp-db.php';

// Override the PHPMailer
global $phpmailer;
require_once(dirname(__FILE__) . '/mock-mailer.php');
$phpmailer = new MockPHPMailer();

/*
 * default_storage_engine and storage_engine are the same option, but storage_engine
 * was deprecated in MySQL (and MariaDB) 5.5.3, and removed in 5.7.
 */
if (version_compare($wpdb->db_version(), '5.5.3', '>=')) {
    $wpdb->query('SET default_storage_engine = InnoDB');
} else {
    $wpdb->query('SET storage_engine = InnoDB');
}
$wpdb->select(DB_NAME, $wpdb->dbh);

error_log('Tables before: ' . print_r($wpdb->tables, true));

/**
 * Before dropping the tables include the active plugins as those might define
 * additional tables that should be dropped.
 **/
foreach ($activePlugins as $activePlugin) {
    include_once WP_PLUGIN_DIR . '/' . $activePlugin;
}

error_log('Tables after: ' . print_r($wpdb->tables, true));

ob_start();

echo "Installing..." . PHP_EOL;

foreach ($wpdb->tables() as $table => $prefixed_table) {
    $wpdb->query("DROP TABLE IF EXISTS $prefixed_table");
}

foreach ($wpdb->tables('ms_global') as $table => $prefixed_table) {
    $wpdb->query("DROP TABLE IF EXISTS $prefixed_table");

    // We need to create references to ms global tables.
    if ($multisite)
        $wpdb->$table = $prefixed_table;
}

// Prefill a permalink structure so that WP doesn't try to determine one itself.
add_action('populate_options', '_set_default_permalink_structure_for_tests');

wp_install(WP_TESTS_TITLE, 'admin', WP_TESTS_EMAIL, true, null, 'password');

// Delete dummy permalink structure, as prefilled above.
if (!is_multisite()) {
    delete_option('permalink_structure');
}
remove_action('populate_options', '_set_default_permalink_structure_for_tests');

if ($multisite) {
    echo "Installing network..." . PHP_EOL;

    define('WP_INSTALLING_NETWORK', true);

    $title = WP_TESTS_TITLE . ' Network';
    $subdomain_install = false;

    install_network();
    populate_network(1, WP_TESTS_DOMAIN, WP_TESTS_EMAIL, $title, '/', $subdomain_install);
    $wp_rewrite->set_permalink_structure('');
}

// finally activate the plugins that should be activated
if (!empty($activePlugins)) {
    foreach ($activePlugins as $plugin) {
        activate_plugin($plugin, null, $multisite, false);
    }
}

return ob_get_clean();