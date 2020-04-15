<?php
/**
 * Installs WordPress for the purpose of the unit-tests
 *
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

$configuration = unserialize(base64_decode($argv[1]));

$multisite = !empty($argv[2]) ? $argv[2] : false;

// Require the autoload file as passed from the configuration.
require_once $configuration['autoload'];

if (!empty($multisite)) {
	tad\WPBrowser\configurePatchwork(
		tad\WPBrowser\isolatedInstallPatchworkConfig( $configuration )
	);

	Patchwork\redefine('is_multisite', function () {
		global $_is_multisite;

		if (empty($_is_multisite)) {
			return Patchwork\relay();
		}

		return true;
	});
}

if (!empty($configuration['activePlugins'])) {
	$activePlugins = $configuration['activePlugins'];
}
else {
	$activePlugins = [];
}

// If Cron is not disabled, then disable it now.
if ( ! isset( $configuration['constants']['DISABLE_WP_CRON'] ) ) {
    print( "Disabling cron\n" );
    $configuration['constants']['DISABLE_WP_CRON'] = true;
} else {
    $enabled = DISABLE_WP_CRON ? 'yes' : 'no';
    print( "Cron disabled (via 'DISABLE_WP_CRON' constant): {$enabled}\n" );
}

printf("\nConfiguration:\n\n%s\n\n", json_encode($configuration, JSON_PRETTY_PRINT));

foreach ($configuration['constants'] as $key => $value) {
	define($key, $value);
}

$table_prefix = WP_TESTS_TABLE_PREFIX;

define('WP_INSTALLING', true);

require_once __DIR__ . '/functions.php';

tests_reset__SERVER();

$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

require_once ABSPATH . '/wp-settings.php';

require_once ABSPATH . '/wp-admin/includes/upgrade.php';
require_once ABSPATH . '/wp-includes/wp-db.php';

// Override the PHPMailer
global $phpmailer;
require_once( __DIR__ . '/mock-mailer.php');
$phpmailer = new MockPHPMailer();

/*
 * default_storage_engine and storage_engine are the same option, but storage_engine
 * was deprecated in MySQL (and MariaDB) 5.5.3, and removed in 5.7.
 */
if (version_compare($wpdb->db_version(), '5.5.3', '>=')) {
	$wpdb->query('SET default_storage_engine = InnoDB');
}
else {
	$wpdb->query('SET storage_engine = InnoDB');
}
$wpdb->select(DB_NAME, $wpdb->dbh);

/**
 * Before dropping/emptying the tables include the active plugins as those might define
 * additional tables that should be dropped.
 **/
foreach ($activePlugins as $activePlugin) {
	printf("Including plugin [%s] files\n", $activePlugin);
	$path = realpath(WP_PLUGIN_DIR . '/' . $activePlugin);
	if (!file_exists($path)) {
		$path = dirname($configuration['root']) . '/' . $activePlugin;
	}
	include_once $path;
}

$wpdb->query("SET FOREIGN_KEY_CHECKS = 0");

// By default empty the tables, do not drop them.
$tables_handling = isset( $configuration['tablesHandling'] ) ?
	$configuration['tablesHandling']
	: 'empty';

switch ( $tables_handling ) {
	default;
	case 'drop':
		echo "\nThe following tables will be dropped: ", "\n\t- ", implode( "\n\t- ", $wpdb->tables ), "\n";
		tad\WPBrowser\dropWpTables( $wpdb );
		break;
	case 'empty':
		echo "\nThe following tables will be emptied: ", "\n\t- ", implode( "\n\t- ", $wpdb->tables ), "\n";
		tad\WPBrowser\emptyWpTables( $wpdb );
		break;
	case 'let':
		echo "\nTables will not be touched.\n";
		// Do nothing, just let the tables be.
		break;
}

foreach ( $wpdb->tables( 'ms_global' ) as $table => $prefixed_table ) {
	// We need to create references to ms global tables.
	if ( $multisite ) {
		$wpdb->$table = $prefixed_table;
	}
}

$wpdb->query( "SET FOREIGN_KEY_CHECKS = 1" );

echo "\n\nInstalling WordPress...\n";

// Prefill a permalink structure so that WP doesn't try to determine one itself.
add_action('populate_options', '_set_default_permalink_structure_for_tests');

$installFilters = new tad\WPBrowser\Module\WPLoader\Filters( $configuration['installationFilters'] );

$installFilters->toRemove()->remove();
$installFilters->toAdd()->add();

wp_install(WP_TESTS_TITLE, 'admin', WP_TESTS_EMAIL, true, null, 'password');

$installFilters->toAdd()->remove();
$installFilters->toRemove()->add();

// Delete dummy permalink structure, as prefilled above.
if (!is_multisite()) {
	delete_option('permalink_structure');
}
remove_action('populate_options', '_set_default_permalink_structure_for_tests');

if ($multisite) {
	echo "Installing network..." . PHP_EOL;

	define('WP_INSTALLING_NETWORK', true);

	$title             = WP_TESTS_TITLE . ' Network';
	$subdomain_install = false;

	install_network();
	populate_network(1, WP_TESTS_DOMAIN, WP_TESTS_EMAIL, $title, '/', $subdomain_install);
	$wp_rewrite->set_permalink_structure('');


	// activate monkey-patching on `is_multisite` using Patchwork, see above
	// this is to allow plugins that could check for `is_multisite` on activation to work as intended
	global $_is_multisite, $current_site;
	$_is_multisite = $multisite;

	// spoof the `$current_site` global
	if (empty($current_site)) {
		$current_site = new stdClass();
	}

	$current_site->id      = 1;
	$current_site->blog_id = 1;
}

// finally activate the plugins that should be activated
if (!empty($activePlugins)) {
	$activePlugins = array_unique($activePlugins);

	if ($multisite) {
		require(ABSPATH . WPINC . '/class-wp-site-query.php');
		require(ABSPATH . WPINC . '/class-wp-network-query.php');
		require(ABSPATH . WPINC . '/ms-blogs.php');
		require(ABSPATH . WPINC . '/ms-settings.php');
	}

	foreach ($activePlugins as $plugin) {
		printf("\n%sctivating plugin [%s]...", $multisite ? 'Network a' : 'A', $plugin);
		activate_plugin($plugin, null, $multisite, false);
	}
}
