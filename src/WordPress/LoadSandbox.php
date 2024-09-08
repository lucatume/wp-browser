<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Utils\MonkeyPatch;
use lucatume\WPBrowser\Utils\Property;
use lucatume\WPBrowser\WordPress\Database\DatabaseInterface;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;

class LoadSandbox
{
    private string $wpRootDir;
    /**
     * @var array{string,int}[]
     */
    private array $redirects = [];
    private string $bufferedOutput = '';

    public function __construct(string $wpRootDir, private string $domain)
    {
        $this->wpRootDir = rtrim($wpRootDir, '/\\');
    }

    /**
     * @throws InstallationException
     */
    public function load(?DatabaseInterface $db = null): void
    {
        $this->setUpServerVars();
        PreloadFilters::addFilter('wp_fatal_error_handler_enabled', [$this, 'returnFalse'], 100);
        PreloadFilters::addFilter('wp_redirect', [$this, 'logRedirection'], 100, 2);
        PreloadFilters::addFilter('wp_die_handler', [$this, 'wpDieHandler']);
        // Setting the `chunk_size` to `0` means the function will only be called when the output buffer is closed.
        ob_start([$this, 'obCallback'], 0);

        // ISSUE #753
        if ($db instanceof MysqlDatabase) {
            // Define the `DB_` constants.
            define('DB_NAME', $db->getDbName());
            define('DB_USER', $db->getDbUser());
            define('DB_PASSWORD', $db->getDbPassword());
            define('DB_HOST', $db->getDbHost());

            // Silence errors about the redeclaration of the `DB_` constants.
            $previousErrorHandler = set_error_handler(callback: static function ($errno, $errstr) {
                if ($errno === E_USER_ERROR && str_contains($errstr, 'Cannot redeclare') && str_contains($errstr, 'DB_')) {
                    return true;
                }

                return false;
            });

            if(class_exists('\Roots\WPConfig\Config')){
                $configArray = new class implements \ArrayAccess {
                    private $configMap = [];

                    public function offsetExists( $offset ) {
                        return isset( $this->configMap[ $offset ] );
                    }

                    public function offsetGet( $offset ) {
                        return $this->configMap[ $offset ];
                    }

                    public function offsetSet( $offset, $value ) {
                        if ( in_array( $offset, [ 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST' ], true ) ) {
                            return;
                        }
                        $this->configMap[ $offset ] = $value;
                    }

                    public function offsetUnset( $offset ) {
                        unset( $this->configMap[ $offset ] );
                    }
                };

                Property::setPrivateProperties( '\Roots\WPConfig\Config', [
                    'configMap' => $configArray
                ] );
        }
        // END ISSUE #753

        // Exceptions thrown during loading are not wrapped on purpose to remove debug overhead.
        include_once $this->wpRootDir . '/wp-load.php';

        // ISSUE #753
        if (!empty($previousErrorHandler)) {
            set_error_handler($previousErrorHandler);
        }
        // END ISSUE #753

        ob_end_clean();
        // If this is reached, then WordPress has loaded correctly.
        remove_filter('wp_fatal_error_handler_enabled', [$this, 'returnFalse'], 100);
        remove_filter('wp_redirect', [$this, 'logRedirection'], 100);
    }

    protected function setUpServerVars(): void
    {
        $serverVars = [
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'HTTP_HOST' => $this->domain,
        ];

        foreach ($serverVars as $key => $value) {
            if (empty($_SERVER[$key])) {
                $_SERVER[$key] = $value;
            }
        }
    }

    /**
     * @throws InstallationException
     */
    public function obCallback(string $buffer): bool
    {
        $this->bufferedOutput .= $buffer;
        $bodyContent = $this->extractBodyContent($buffer);
        class_exists(InstallationException::class);

        if (!function_exists('did_action')) {
            throw InstallationException::becauseWordPressFailedToLoad($bodyContent);
        }

        // From this point on, some WordPress functions are available depending on where the loading failed.

        if (did_action('wp_loaded') >= 1) {
            return true;
        }
        $reason = 'action wp_loaded not fired.';

        if (count($this->redirects) > 0
            && $this->redirects[0][1] === 302
            && parse_url($this->redirects[0][0], PHP_URL_PATH) === '/wp-admin/install.php'
        ) {
            // Single site install redirection.
            throw InstallationException::becauseWordPressIsNotInstalled();
        }

        if (function_exists('is_multisite')
            && function_exists('is_subdomain_install')
            && function_exists('ms_load_current_site_and_network')
            && is_multisite()
        ) {
            $isSubdomainInstall = is_subdomain_install();
            $currentSiteAndNetwork = ms_load_current_site_and_network($this->domain, '', $isSubdomainInstall);

            if ($isSubdomainInstall && is_string($currentSiteAndNetwork)) {
                // Multisite sub-domain install redirection to /wp-signup.php?new=<domain>.
                // This does not use `wp_redirect()`, so check the URL.
                $path = parse_url($currentSiteAndNetwork, PHP_URL_PATH);
                $query = parse_url($currentSiteAndNetwork, PHP_URL_QUERY);
                if ($path === '/wp-signup.php' && $query === 'new=' . $this->domain) {
                    throw InstallationException::becauseWordPressMultsiteIsNotInstalled(true);
                }
            } elseif (!$isSubdomainInstall && $currentSiteAndNetwork === false) {
                // Multisite sub-directory installation, provides a misleading error message about `dead_db`.
                throw InstallationException::becauseWordPressMultsiteIsNotInstalled(false);
            }
        }

        // We do not know what happened, throw and try to be helpful.
        throw InstallationException::becauseWordPressFailedToLoad($bodyContent ?: $reason);
    }

    public function logRedirection(string $location, int $status): string
    {
        $this->redirects[] = [$location, $status];

        return $location;
    }

    public function returnFalse(): bool
    {
        return false;
    }

    public function getBufferedOutput(): string
    {
        return $this->bufferedOutput;
    }

    private function extractBodyContent(string $buffer): string
    {
        $bodyStart = strpos($buffer, '<body');
        $bodyEnd = strpos($buffer, '</body>');
        if (false === $bodyStart || false === $bodyEnd) {
            $bodyStart = 0;
            $bodyEnd = strlen($buffer);
        }

        return trim(strip_tags(substr($buffer, $bodyStart, $bodyEnd - $bodyStart)));
    }

    public function wpDieHandler(): callable
    {
        return [$this, 'obCallback'];
    }
}
