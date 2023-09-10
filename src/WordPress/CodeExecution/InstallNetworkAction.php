<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequest;
use lucatume\WPBrowser\WordPress\PreloadFilters;
use WP_Error;
use function install_network;

class InstallNetworkAction implements CodeExecutionActionInterface
{
    private FileRequest $request;

    public function __construct(
        FileRequest $request,
        string $wpRootDir,
        string $adminEmail,
        string $title,
        bool $subdomain
    ) {
        $request
            ->setTargetFile($wpRootDir . '/wp-admin/admin.php')
            ->defineConstant('MULTISITE', false)
            ->defineConstant('WP_INSTALLING_NETWORK', true)
            ->addPreloadClosure(function () use ($subdomain): void {
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

                if ($subdomain) {
                    PreloadFilters::spoofDnsWildcardCheck();
                }
            })
            ->addAfterLoadClosure(fn() => $this->installWordPressNetwork($adminEmail, $title, $subdomain));

        $this->request = $request;
    }

    public function getClosure(): Closure
    {
        $request = $this->request;

        return static function () use ($request): mixed {
            return $request->execute();
        };
    }

    /**
     * @throw RuntimeException If the network could not be installed.
     */
    private function installWordPressNetwork(string $email, string $sitename, bool $subdomain): void
    {
        global $wpdb;
        foreach ($wpdb->tables('ms_global') as $table => $prefixed_table) {
            $wpdb->$table = $prefixed_table;
        }
        // Set the current user to the admin user.
        wp_set_current_user(1);
        require_once ABSPATH . '/wp-admin/includes/network.php';
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        install_network();
        $result = populate_network(
            1,
            get_clean_basedomain(),
            sanitize_email($email),
            wp_unslash($sitename),
            '/',
            $subdomain
        );

        if ($result instanceof WP_Error) {
            throw new RuntimeException('Could not install WordPress network: ' . $result->get_error_message());
        }

        $tables = $wpdb->get_col('SHOW TABLES');
        $missingTables = array_diff($wpdb->tables('ms_global'), $tables);
        if (count($missingTables)) {
            throw new RuntimeException('Could not install WordPress network: ' . implode(
                ', ',
                $missingTables
            ) . ' table(s) not found.');
        }
    }
}
