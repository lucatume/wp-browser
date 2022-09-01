<?php

namespace lucatume\WPBrowser\WordPress\FileRequests;

use Closure;

class FileRequestClosureFactory
{
    private FileRequestFactory $requestFactory;

    public function __construct(FileRequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    public function toActivatePlugin(string $plugin, array $config, int $adminUserId = 1): Closure
    {
        $request = $this->requestFactory->buildGetRequest('/wp-admin/plugins.php', [
            '_wpnonce' => static fn() => wp_create_nonce('activate-plugin_' . $plugin),
            'action' => 'activate',
            'paged' => 1,
            'plugin' => $plugin,
            's' => ''
        ], 1);

        return static function () use ($request): void {
            $request->execute();
        };
    }

    public function toSwitchTheme(string $stylesheet, array $config, int $adminUserId = 1): Closure
    {
        $request = $this->requestFactory->buildGetRequest('/wp-admin/themes.php', [
            '_wpnonce' => static fn() => wp_create_nonce('switch-theme_' . $stylesheet),
            'action' => 'activate',
            'stylesheet' => $stylesheet
        ], 1);

        return static function () use ($request): void {
            $request->execute();
        };
    }

    public function toInstall(string $title, string $username, string $password, string $email): Closure
    {
        $request = $this->requestFactory->buildPostRequest('/wp-admin/install.php', [
            'step' => 2,
            'weblog_title' => $title,
            'user_name' => $username,
            'admin_password' => $password,
            'admin_password2' => $password,
            'admin_email' => $email,
            'blog_public' => 1,
        ], 0);

        return static function () use ($request): void {
            $request->execute();
        };
    }
}
