<?php
/**
 * Functions to wrap and interact with the antecedent/patchwork library.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

/**
 * Includes the Patchwork library main file.
 *
 * @return void
 */
function includePatchwork()
{
    if (function_exists('Patchwork\redefine')) {
        return;
    }

    require_once vendorDir('antecedent/patchwork/Patchwork.php');
}

/**
 * Configures Patchwork using its API, without requiring a file.
 *
 * @param array<string,mixed> $config An array defining the Patchwork configuration to use.
 *
 * @return void
 */
function configurePatchwork($config)
{
    includePatchwork();
    \Patchwork\Config\set($config, __FILE__);
}

/**
 * Generates the Patchwork configuration that will be used in the `isolate-installation.php` script.
 *
 * @param array<string,mixed> $envConfig The current environment configuration.
 *
 * @return array<string,mixed> The Patchwork configuration to use in the `isolated-installation.php` script.
 */
function isolatedInstallPatchworkConfig(array $envConfig)
{
    $config = [
        'blacklist' => [
            // Exclude the whole WordPress folder by default.
            rtrim($envConfig['constants']['ABSPATH'], '/'),
            // Exclude the project root folder too.
            rtrim($envConfig['root'], '/'),
        ],
        // But include the `wp-includes/load.php` file that defines the function we need to redefine.
        'whitelist' => [ $envConfig['constants']['ABSPATH'] . 'wp-includes/load.php' ],
    ];

    foreach ([ 'WP_PLUGIN_DIR', 'WP_CONTENT_DIR', 'WPMU_PLUGIN_DIR', 'WP_TEMP_DIR' ] as $const) {
        if (isset($envConfig['constants'][ $const ]) && file_exists($envConfig['constants'][ $const ])) {
            $config['blacklist'][] = rtrim($envConfig['constants'][ $const ], '/');
        }
    }

    return $config;
}
