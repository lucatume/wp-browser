<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequest;
use function wp_slash;

class InstallAction implements CodeExecutionActionInterface
{
    /**
     * @var \lucatume\WPBrowser\WordPress\FileRequests\FileRequest
     */
    private $request;

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
            ->runInFastMode($wpRootDir)
            ->setTargetFile($wpRootDir . '/wp-load.php')
            ->defineConstant('WP_INSTALLING', true)
            ->defineConstant('MULTISITE', false)
            ->addPreloadClosure(function () use ($url): void {
                // The `MULTISITE` const might be already defined in the `wp-config.php` file.
                // If that is the case, silence the error.
                set_error_handler(static function ($errno, $errstr): bool {
                    if (strpos($errstr, 'MULTISITE already defined') !== false) {
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
            ->addAfterLoadClosure(function () use ($title, $adminUser, $adminPassword, $adminEmail) {
                return $this->installWordPress($title, $adminUser, $adminPassword, $adminEmail);
            });
        $this->request = $request;
    }

    private function installWordPress(string $title, string $adminUser, string $adminPassword, string $adminEmail): void
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        require_once ABSPATH . '/wp-includes/class-wpdb.php';
        // Set the permalink structure to a default one to avoid the `wp_install_maybe_enable_pretty_permalinks` time.
        update_option('permalink_structure', '/%year%/%monthnum%/%day%/%postname%/');
        $fixPermalinkStructure = function () {
            return '/%year%/%monthnum%/%day%/%postname%/';
        };
        add_filter('pre_option_permalink_structure', $fixPermalinkStructure);
        $installed = wp_install($title, $adminUser, $adminEmail, true, '', wp_slash($adminPassword));
        // Update again as it might have been reset during the installation
        remove_filter('pre_option_permalink_structure', $fixPermalinkStructure);
        update_option('permalink_structure', '/%year%/%monthnum%/%day%/%postname%/');
    }

    public function getClosure(): Closure
    {
        $request = $this->request;

        return static function () use ($request) {
            return $request->execute();
        };
    }
}
