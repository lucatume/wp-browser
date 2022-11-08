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

    public function toActivatePlugin(string $plugin): Closure
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

    public function toSwitchTheme(string $stylesheet): Closure
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

    public function toInstall(
        string $title,
        string $username,
        string $password,
        string $email,
        ?string $url = null
    ): Closure
    {
        $request = $this->requestFactory->buildPostRequest('/wp-admin/install.php?step=2', [
            'weblog_title' => $title,
            'user_name' => $username,
            'admin_password' => $password,
            'admin_password2' => $password,
            'admin_email' => $email,
            'blog_public' => 1,
        ], 0);

        if($url !== null){
            $request->setServerVar('HTTP_HOST', $url);
        }

        // Avoid any mail being sent during the installation.
        $request->addPreloadClosure(static function () {
            if (!function_exists('wp_mail')) {
                function wp_mail()
                {
                    return true;
                }
            }
        });

        return static function () use ($request): void {
            $request->execute();
        };
    }
}
