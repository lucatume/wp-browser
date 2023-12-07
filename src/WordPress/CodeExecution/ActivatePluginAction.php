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
    /**
     * @var \lucatume\WPBrowser\WordPress\FileRequests\FileRequest
     */
    private $request;

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
            ->addAfterLoadClosure(function () use ($plugin, $multisite, $silent) {
                return $this->activatePlugin($plugin, $multisite, $silent);
            });
        $this->request = $request;
    }

    /**
     * @throws InstallationException
     */
    private function activatePlugin(string $plugin, bool $multisite, bool $silent = false): void
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $activated = activate_plugin($plugin, '', $multisite, $silent);
        $activatedString = $multisite ? 'network activated' : 'activated';
        $message = "Plugin $plugin could not be $activatedString.";

        if ($activated instanceof WP_Error) {
            $message = $activated->get_error_message();
            $data = $activated->get_error_data();
            if ($data && is_string($data)) {
                $message .= ": $data";
            }
            throw new InstallationException(trim($message));
        }

        $isActive = $multisite ? is_plugin_active_for_network($plugin) : is_plugin_active($plugin);

        if (!$isActive) {
            throw new RuntimeException($message);
        }
    }

    public function getClosure(): Closure
    {
        $request = $this->request;

        return static function () use ($request) {
            return $request->execute();
        };
    }
}
