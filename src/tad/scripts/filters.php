<?php

/**
 * Filter the `status_header` actiion to log response stati set during WordPress execution.
 */
tests_add_filter('status_header', function ($_, $code) {
    global $cli_statuses;
    $cli_statuses[] = $code;
}, 10, 2);

// Block update checks during tests
$just_now = function () {
    return (object)['last_checked' => time(), 'version_checked' => $GLOBALS['wp_version']];
};
tests_add_filter('pre_site_transient_update_core', $just_now, 10, 1);
tests_add_filter('pre_site_transient_update_plugins', $just_now, 10, 1);
tests_add_filter('pre_site_transient_update_themes', $just_now, 10, 1);

// Block the browser update check
tests_add_filter('pre_site_transient_browser_' . md5('Symfony2 BrowserKit'), function () {
    return true;
}, 10, 1);

// Block the dashboard feed fetching
tests_add_filter('init', function () {
    add_filter('pre_transient_dash_' . md5('dashboard_primary_' . get_locale()), function () {
        return 'Disabled';
    });
});

// Block the compression test check
tests_add_filter('pre_option_can_compress_scripts', function () {
    return 1;
});
