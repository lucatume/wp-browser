<?php

namespace lucatume\WPBrowser\Module\WPLoader;

use Closure;
use lucatume\WPBrowser\Process\Worker\Exited;
use lucatume\WPBrowser\Utils\CorePHPUnit;
use lucatume\WPBrowser\Utils\ErrorHandling;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use lucatume\WPBrowser\WordPress\Auth as WordPressAuth;
use Throwable;

class ClosureFactory
{

    private string $wpRootDir;
    /**
     * @var callable|null
     */
    private $debugFn = null;
    private WordPressAuth $auth;

    public function __construct(string $wpRootDir, ?WordPressAuth $auth = null)
    {
        $this->wpRootDir = $wpRootDir;
        $this->auth = $auth ?? new WordPressAuth();
    }

    public function toActivatePlugin(string $plugin, array $config, int $adminUserId = 1): Closure
    {
        [$authCookieName, $authCookieValue] = $this->auth->getAuthCookieForUserId($adminUserId);

        $pluginsFile = $this->wpRootDir . '/wp-admin/plugins.php';
        $wpConfigFile = ABSPATH . 'wp-config.php';
        $testsWpConfigFile = CorePHPUnit::path('/wp-tests-config.php');
        $moduleConfig = $config;
        $nonce = wp_create_nonce('activate-plugin_' . $plugin);
        $requestUri = add_query_arg([
            'action' => 'activate',
            'plugin' => 'plugin',
            '_wpnonce' => $nonce,
            'status' => 'activated',
            'page' => 1,
            's' => ''
        ], '/wp-admin/plugins.php');

        return static function () use (
            $authCookieName,
            $authCookieValue,
            $moduleConfig,
            $nonce,
            $plugin,
            $pluginsFile,
            $requestUri,
            $testsWpConfigFile,
            $wpConfigFile
        ): void {
            $wpLoaderConfig = $moduleConfig;
            $wpLoaderIncludeWpSettings = true;
            MonkeyPatch::redirectFileToFile($wpConfigFile, $testsWpConfigFile);
            ErrorHandling::throwWarnings();
            // Reveal the errors.
            define('WP_DISABLE_FATAL_ERROR_HANDLER', true);
            $_COOKIE[$authCookieName] = $authCookieValue;
            // Simulate an activation from the Plugins screen.
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_SERVER['REQUEST_URI'] = $requestUri;
            $_GET['plugin'] = $plugin;
            $_GET['action'] = 'activate';
            $_GET['_wpnonce'] = $nonce;
            $_GET['status'] = 'activated';
            $_GET['page'] = 1;
            $_GET['s'] = '';
            include $pluginsFile;
        };
    }

    public function toDebugActivationResult(): Closure
    {
        if ($this->debugFn === null) {
            return static function () {
            };
        }

        return function (Exited $exited): void {
            $id = $exited->getId();
            $exitCode = $exited->getExitCode();

            if (str_starts_with($id, 'plugin::')) {
                $format = 'Plugin %s activation: %s';
                $name = substr($id, 8);
            } else {
                $format = 'Theme %s activation: %s';
                $name = substr($id, 12);
            }

            if ($exitCode === 0) {
                $result = 'OK';
            } else {
                $stdout = $exited->getStdout();
                $stderr = $exited->getStderr();
                $result = "FAILED\n\tExit code: $exitCode\n\tSTDOUT: $stdout\n\tSTDERR: $stderr";
                $returnValue = $exited->getReturnValue();
                if ($returnValue instanceof Throwable) {
                    $traceAsString = str_replace("\n", "\n\t\t", $returnValue->getTraceAsString());
                    $result .= "\n\tError: {$returnValue->getMessage()}\n\t\t" . $traceAsString;
                }
            }
            $message = sprintf($format, $name, $result);
            ($this->debugFn)($message);
        };
    }

    public function toSwitchTheme(string $stylesheet, array $config, int $adminUserId = 1): Closure
    {
        [$authCookieName, $authCookieValue] = $this->auth->getAuthCookieForUserId($adminUserId);
        $themesFile = $this->wpRootDir . '/wp-admin/themes.php';
        $wpConfigFile = ABSPATH . 'wp-config.php';
        $testsWpConfigFile = CorePHPUnit::path('/wp-tests-config.php');
        $moduleConfig = $config;
        $nonce = wp_create_nonce('switch-theme_' . $stylesheet);
        $requestUri = add_query_arg([
            'action' => 'activate',
            'stylesheet' => 'stylesheet',
            '_wpnonce' => $nonce
        ], '/wp-admin/themes.php');

        return static function () use (
            $authCookieName,
            $authCookieValue,
            $moduleConfig,
            $nonce,
            $requestUri,
            $stylesheet,
            $testsWpConfigFile,
            $themesFile,
            $wpConfigFile
        ): void {
            $wpLoaderConfig = $moduleConfig;
            $wpLoaderIncludeWpSettings = true;
            MonkeyPatch::redirectFileToFile($wpConfigFile, $testsWpConfigFile);
            ErrorHandling::throwWarnings();
            // Reveal the errors.
            define('WP_DISABLE_FATAL_ERROR_HANDLER', true);
            $_COOKIE[$authCookieName] = $authCookieValue;
            // Simulate an activation from the Themes screen.
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_SERVER['REQUEST_URI'] = $requestUri;
            $_GET['stylesheet'] = $stylesheet;
            $_GET['action'] = 'activate';
            $_GET['_wpnonce'] = $nonce;
            include $themesFile;
        };
    }

    public function setDebugFn(callable $debugFn): ClosureFactory
    {
        $this->debugFn = $debugFn;
        return $this;
    }
}
