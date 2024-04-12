<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequest;
use lucatume\WPBrowser\WordPress\Traits\WordPressChecks;
use lucatume\WPBrowser\WordPress\WPConfigFile;

use function wp_slash;

class InstallAction implements CodeExecutionActionInterface
{
    use WordPressChecks;

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
            ->runInFastMode($wpRootDir)
            ->setTargetFile($wpRootDir . '/wp-load.php')
            ->defineConstant('WP_INSTALLING', true)
            ->defineConstant('MULTISITE', false)
            ->addPreloadClosure(function (): void {
                // The `MULTISITE` const might be already defined in the `wp-config.php` file.
                // If that is the case, silence the error.
                set_error_handler(static function ($errno, $errstr): bool {
                    if (str_contains($errstr, 'MULTISITE already defined')) {
                        return true;
                    }
                    return false;
                });

                // Plug the `auth_redirect` function to avoid the redirect to the login page.
                require_once dirname(__DIR__, 3) . '/includes/pluggables/function-auth-redirect.php';

                // Plug the `wp_mail` function to avoid the sending of emails.
                require_once dirname(__DIR__, 3) . '/includes/pluggables/function-wp-mail.php';
            })
            ->addAfterLoadClosure(fn() => $this->installWordPress($title, $adminUser, $adminPassword, $adminEmail));

        // Define the `WP_SITEURL` constant if not already defined in the wp-config.php file.
        $wpConfigFilePath = $this->findWpConfigFilePath($wpRootDir);
        if ($wpConfigFilePath) {
            $wpConfigFile = new WPConfigFile($wpRootDir, $wpConfigFilePath);
            if (!$wpConfigFile->getConstant('WP_SITEURL')) {
                $request->defineConstant('WP_SITEURL', $url);
            }
        }

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
        // Set the permalink structure to a default one to avoid the `wp_install_maybe_enable_pretty_permalinks` time.
        update_option('permalink_structure', '/%year%/%monthnum%/%day%/%postname%/');
        $fixPermalinkStructure = fn() => '/%year%/%monthnum%/%day%/%postname%/';
        add_filter('pre_option_permalink_structure', $fixPermalinkStructure);
        $installed = wp_install($title, $adminUser, $adminEmail, true, '', wp_slash($adminPassword));
        // Update again as it might have been reset during the installation
        remove_filter('pre_option_permalink_structure', $fixPermalinkStructure);
        update_option('permalink_structure', '/%year%/%monthnum%/%day%/%postname%/');
    }

    public function getClosure(): Closure
    {
        $request = $this->request;

        return static function () use ($request): mixed {
            return $request->execute();
        };
    }
}
