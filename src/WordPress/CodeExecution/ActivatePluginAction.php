<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequest;

class ActivatePluginAction implements CodeExecutionActionInterface
{
    private FileRequest $request;

    public function __construct(
        FileRequest $request,
        string $wpRootDir,
        string $plugin,
        bool $multisite
    ) {
        $request->setTargetFile($wpRootDir . '/wp-load.php')
            ->defineConstant('MULTISITE', $multisite)
            ->addAfterLoadClosure(fn() => $this->activatePlugin($plugin, $multisite));
        $this->request = $request;
    }

    private function activatePlugin(string $plugin, bool $multisite): void
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $activated = \activate_plugin($plugin, '', $multisite);
        $activatedString = $multisite ? 'network activated' : 'activated';
        $message = "Plugin {$plugin} could not be $activatedString.";

        if ($activated instanceof \WP_Error) {
            $message .= ' ' . $activated->get_error_message();
            throw new RuntimeException($message);
        }

        $isActive = $multisite ? is_plugin_active_for_network($plugin) : is_plugin_active($plugin);

        if (!$isActive) {
            throw new RuntimeException($message);
        }
    }

    public function getClosure(): \Closure
    {
        $request = $this->request;

        return static function () use ($request): mixed {
            return $request->execute();
        };
    }
}
