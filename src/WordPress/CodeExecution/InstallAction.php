<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use lucatume\WPBrowser\WordPress\FileRequests\FileRequest;

class InstallAction implements CodeExecutionActionInterface
{
    private FileRequest $request;

    public function __construct(
        FileRequest $request,
        string $wpRootDir,
        string $title,
        string $adminUser,
        string $adminPassword,
        string $adminEmail,
        string $url
    ) {

        $request
            ->setTargetFile($wpRootDir . '/wp-load.php')
            ->defineConstant('WP_INSTALLING', true)
            ->defineConstant('MULTISITE', false)
            ->addPreloadClosure(function () use ($url) {
                // The `MULTISITE` const might be already defined in the `wp-config.php` file.
                // If that is the case, silence the error.
                set_error_handler(static function ($errno, $errstr) {
                    if (str_contains($errstr, 'MULTISITE already defined')) {
                        return true;
                    }
                    return false;
                });

                // Plug the `auth_redirect` function to avoid the redirect to the login page.
                require_once dirname(__DIR__, 3) . '/includes/pluggables/function-auth-redirect.php';

                // Plug the `wp_mail` function to avoid the sending of emails.
                require_once dirname(__DIR__, 3) . '/includes/pluggables/function-wp-mail.php';

                if (!defined('WP_SITEURL')) {
                    define('WP_SITEURL', $url);
                }
            })
            ->addAfterLoadClosure(fn() => $this->installWordPress($title, $adminUser, $adminPassword, $adminEmail));
        $this->request = $request;
    }

    private function installWordPress(
        string $title,
        string $adminUser,
        string $adminPassword,
        string $adminEmail,
    ): void {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        require_once ABSPATH . '/wp-includes/class-wpdb.php';
        wp_install($title, $adminUser, $adminEmail, true, '', \wp_slash($adminPassword));
    }

    public function getClosure(): \Closure
    {
        $request = $this->request;

        return static function () use ($request): mixed {
            return $request->execute();
        };
    }
}
