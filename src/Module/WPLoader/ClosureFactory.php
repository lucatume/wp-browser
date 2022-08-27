<?php

namespace lucatume\WPBrowser\Module\WPLoader;

use Closure;
use lucatume\WPBrowser\Exceptions\SerializableThrowable;
use lucatume\WPBrowser\Process\Worker\Exited;
use lucatume\WPBrowser\Utils\CorePHPUnit;
use lucatume\WPBrowser\Utils\ErrorHandling;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use lucatume\WPBrowser\WordPress\Preload as WordPressPreload;
use lucatume\WPBrowser\WordPress\WPLoaded;
use Throwable;

class ClosureFactory
{

    private string $wpRootDir;
    /**
     * @var callable|null
     */
    private $debugFn = null;
    private WPLoaded $wpLoaded;

    public function __construct(string $wpRootDir, ?WPLoaded $wpLoaded = null)
    {
        $this->wpRootDir = $wpRootDir;
        $this->wpLoaded = $wpLoaded ?? new WPLoaded();
    }

    public function toActivatePlugin(string $plugin, array $config, int $adminUserId = 1): Closure
    {
        [$authCookieName, $authCookieValue] = $this->wpLoaded->getAuthCookieForUserId($adminUserId);

        $pluginsFile = $this->wpRootDir . '/wp-admin/plugins.php';
        $wpConfigFile = ABSPATH . 'wp-config.php';
        $testsWpConfigFile = CorePHPUnit::path('/wp-tests-config.php');
        $moduleConfig = $config;
        $nonce = wp_create_nonce('activate-plugin_' . $plugin);
        $requestUri = add_query_arg([
            '_wpnonce' => $nonce,
            'action' => 'activate',
            'paged' => 1,
            'plugin' => $plugin,
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
            /** @noinspection PhpUnusedLocalVariableInspection */
            global $status, $page;
            $wpLoaderConfig = $moduleConfig;
            $wpLoaderIncludeWpSettings = true;
            MonkeyPatch::redirectFileToFile($wpConfigFile, $testsWpConfigFile);
            ErrorHandling::throwErrors(ErrorHandling::E_All_WARNINGS);
            WordPressPreload::filterWpDieHandlerToExit();
            // Reveal the errors.
            define('WP_DISABLE_FATAL_ERROR_HANDLER', true);
            $_COOKIE[$authCookieName] = $authCookieValue;
            // Simulate an activation from the Plugins screen.
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_SERVER['REQUEST_URI'] = $requestUri;
            $_GET['_wpnonce'] = $nonce;
            $_GET['action'] = 'activate';
            $_GET['paged'] = 1;
            $_GET['plugin'] = $plugin;
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
                $format = 'Activating Plugin %s: %s';
                $name = substr($id, 8);
            } else {
                $format = 'Switching to theme %s: %s';
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
                    $errorClass = $returnValue instanceof SerializableThrowable ?
                        $returnValue->getWrappedThrowableClass()
                        : get_class($returnValue);
                    $result .= "\n\tThrown $errorClass:\n\t\t{$returnValue->getMessage()}\n\t\t" . $traceAsString;
                }
            }
            $message = sprintf($format, $name, $result);
            ($this->debugFn)($message);
        };
    }

    public function toSwitchTheme(string $stylesheet, array $config, int $adminUserId = 1): Closure
    {
        [$authCookieName, $authCookieValue] = $this->wpLoaded->getAuthCookieForUserId($adminUserId);
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
            ErrorHandling::throwErrors(ErrorHandling::E_All_WARNINGS);
            WordPressPreload::filterWpDieHandlerToExit();
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
