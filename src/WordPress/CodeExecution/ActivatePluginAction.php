<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequest;
use lucatume\WPBrowser\WordPress\InstallationException;
use WP_Error;

use function activate_plugin;

class ActivatePluginAction implements CodeExecutionActionInterface
{
    private FileRequest $request;

    public function __construct(
        FileRequest $request,
        string $wpRootDir,
        string $plugin,
        bool $multisite,
        bool $silent = false
    ) {
        $request->setTargetFile($wpRootDir . '/wp-load.php')
            ->runInFastMode($wpRootDir)
            ->defineConstant('MULTISITE', $multisite)
            ->addAfterLoadClosure(fn() => $this->activatePlugin($plugin, $multisite, $silent));
        $this->request = $request;
    }

    /**
     * @throws InstallationException
     */
    private function activatePlugin(string $plugin, bool $multisite, bool $silent = false): void
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        if (file_exists(WP_PLUGIN_DIR . '/' . $plugin)) {
            $activated = activate_plugin($plugin, '', $multisite, $silent);
        } else {
            [$activated, $plugin] = $this->activateExternalPlugin($plugin, $multisite, $silent);
        }

        $activatedString = $multisite ? 'network activated' : 'activated';
        $message = "Plugin $plugin could not be $activatedString.";

        if ($activated instanceof WP_Error) {
            $message = $activated->get_error_message();
            $data = $activated->get_error_data();
            if ($data && is_string($data)) {
                $message = substr($message, 0, -1) . ": $data";
            }
            throw new InstallationException(trim($message));
        }

        $isActive = $multisite ? is_plugin_active_for_network($plugin) : is_plugin_active($plugin);

        if (!$isActive) {
            throw new RuntimeException($message);
        }
    }

    /**
     * @return array{0: bool|WP_Error, 1: string}
     */
    private function activateExternalPlugin(
        string $plugin,
        bool $multisite,
        bool $silent = false
    ): array {
        ob_start();
        try {
            $pluginRealpath = realpath($plugin);

            if (!$pluginRealpath) {
                return [new \WP_Error('plugin_not_found', "Plugin file $plugin does not exist."), ''];
            }

            // Get the plugin name in the `plugin/plugin-file.php` format.
            $pluginWpName = basename(dirname($pluginRealpath)) . '/' . basename($pluginRealpath);

            include_once $pluginRealpath;

            if (!$silent) {
                do_action('activate_plugin', $pluginWpName, $multisite);
                $pluginNameForActivationHook = ltrim($pluginRealpath, '\\/');
                do_action("activate_{$pluginNameForActivationHook}", $multisite);
            }

            $activePlugins = $multisite ? get_site_option('active_sitewide_plugins') : get_option('active_plugins');

            if (!is_array($activePlugins)) {
                $activePlugins = [];
            }

            if ($multisite) {
                // Network-activated plugins are stored in the format <plugins_name> => <timestamp>.
                $activePlugins[$pluginWpName] = time();
                update_site_option('active_sitewide_plugins', $activePlugins);
            } else {
                $activePlugins[] = $pluginWpName;
                update_option('active_plugins', $activePlugins);
            }
        } catch (\Throwable $t) {
            return [new \WP_Error('plugin_activation_failed', $t->getMessage()), ''];
        }

        $output = ob_get_clean();

        if ($output) {
            return [new \WP_Error('plugin_activation_output', $output), $pluginWpName];
        }

        return [true, $pluginWpName];
    }

    public function getClosure(): Closure
    {
        $request = $this->request;

        return static function () use ($request): mixed {
            return $request->execute();
        };
    }
}
