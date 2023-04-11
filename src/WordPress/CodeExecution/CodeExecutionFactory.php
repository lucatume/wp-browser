<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;
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
            ->addAfterLoadClosure(static function () use ($multisite) {
                return is_blog_installed() && (!$multisite || is_multisite());
            });

        return static function () use ($request): mixed {
            return $request->execute();
        };
    }

    public function toActivatePlugin(mixed $plugin, mixed $multisite): Closure
    {
        $request = $this->requestFactory->buildGetRequest()
            ->blockHttpRequests()
            ->setTargetFile($this->wpRootDir . '/wp-load.php')
            ->setRedirectFiles($this->redirectFiles)
            ->addPresetGlobalVars($this->presetGlobalVars)
            ->addAfterLoadClosure(static function () use ($plugin, $multisite) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
                $activated = \activate_plugin($plugin, '', $multisite, false);
                if ($activated instanceof \WP_Error) {
                    throw new \RuntimeException($activated->get_error_message());
                }
            });

        return static function () use ($request): mixed {
            return $request->execute();
        };
    }

    public function toSwitchTheme(mixed $stylesheet): Closure
    {
        $request = $this->requestFactory->buildGetRequest()
            ->blockHttpRequests()
            ->setTargetFile($this->wpRootDir . '/wp-load.php')
            ->setRedirectFiles($this->redirectFiles)
            ->addPresetGlobalVars($this->presetGlobalVars)
            ->addAfterLoadClosure(static function () use ($stylesheet) {
                \switch_theme($stylesheet);
            });

        return static function () use ($request): mixed {
            return $request->execute();
        };
    }

    public function getAdminUserId(): int
    {
        return $this->adminUserId;
    }

    public function setAdminUserId(int $adminUserId): self
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }
}
