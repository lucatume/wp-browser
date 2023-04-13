<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequestFactory;

class CodeExecutionFactory
{
    private string $wpRootDir;
    private FileRequestFactory $requestFactory;
    private array $redirectFiles;
    private array $presetGlobalVars;
    private int $adminUserId = 1;

    public function __construct(
        string $wpRootDir,
        string $domain,
        array $redirectFiles = [],
        array $presetGlobalVars = []
    ) {
        $this->wpRootDir = rtrim($wpRootDir, '\\/');
        $this->requestFactory = new FileRequestFactory($wpRootDir, $domain);
        $this->redirectFiles = $redirectFiles;
        $this->presetGlobalVars = $presetGlobalVars;
    }

    public function toCheckIfWpIsInstalled(bool $multisite): Closure
    {
        $request = $this->requestFactory->buildGetRequest()
            ->blockHttpRequests()
            ->setTargetFile($this->wpRootDir . '/wp-load.php')
            ->setRedirectFiles($this->redirectFiles)
            ->addPresetGlobalVars($this->presetGlobalVars)
            ->addAfterLoadClosure(fn() => $this->isBlogInstalled($multisite));

        return static function () use ($request): mixed {
            return $request->execute();
        };
    }

    public function toActivatePlugin(mixed $plugin, bool $multisite): Closure
    {
        $request = $this->requestFactory->buildGetRequest()
            ->blockHttpRequests()
            ->setTargetFile($this->wpRootDir . '/wp-load.php')
            ->setRedirectFiles($this->redirectFiles)
            ->addPresetGlobalVars($this->presetGlobalVars)
            ->defineConstant('MULTISITE', $multisite)
            ->addAfterLoadClosure(fn() => $this->activatePlugin($plugin, $multisite));

        return static function () use ($request): mixed {
            return $request->execute();
        };
    }

    public function toSwitchTheme(mixed $stylesheet, bool $multisite): Closure
    {
        $request = $this->requestFactory->buildGetRequest()
            ->blockHttpRequests()
            ->setTargetFile($this->wpRootDir . '/wp-load.php')
            ->setRedirectFiles($this->redirectFiles)
            ->addPresetGlobalVars($this->presetGlobalVars)
            ->defineConstant('MULTISITE', $multisite)
            ->addAfterLoadClosure(fn() => $this->switchTheme($stylesheet, $multisite));

        return static function () use ($request): mixed {
            return $request->execute();
        };
    }

    private function activatePlugin(mixed $plugin, bool $multisite): void
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

    private function isBlogInstalled(bool $multisite): bool
    {
        return is_blog_installed() && (!$multisite || is_multisite());
    }

    private function switchTheme(mixed $stylesheet, bool $multisite): void
    {
        // The `switch_theme` function will not complain about a missing theme: check it now.
        $theme = \wp_get_theme($stylesheet);
        if (!($theme instanceof \WP_Theme && $theme->exists())) {
            throw new RuntimeException("Theme $stylesheet does not exist.");
        }

        if ($multisite) {
            \WP_Theme::network_enable_theme($stylesheet);
        }

        \switch_theme($stylesheet);
    }
}
