<?php

namespace lucatume\WPBrowser\Module\WPLoader;

use Closure;
use lucatume\WPBrowser\Process\Worker\Exited;
use lucatume\WPBrowser\Utils\CorePHPUnit;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use Throwable;

class ClosureFactory
{

    private string $wpRootDir;
    private int $adminUserId;
    /**
     * @var callable
     */
    private $debugFn;
    private array $config;

    public function __construct(string $wpRootDir, int $adminUserId, callable $debugFn, array $config)
    {
        $this->wpRootDir = $wpRootDir;
        $this->adminUserId = $adminUserId;
        $this->debugFn = $debugFn;
        $this->config = $config;
    }

    public function toActivatePlugin(string $plugin): Closure
    {
        wp_set_current_user($this->adminUserId);
        $pluginsFile = $this->wpRootDir . '/wp-admin/plugins.php';
        $nonce = wp_create_nonce('activate-plugin_' . $plugin);
        $authCookieName = AUTH_COOKIE;
        $authCookieValue = wp_generate_auth_cookie($this->adminUserId, time() + 3600);
        $wpConfigFile = ABSPATH . 'wp-config.php';
        $testsWpConfigFile = CorePHPUnit::path('/wp-tests-config.php');
        $moduleConfig = $this->config;
        $requestUri = add_query_arg([
            'action' => 'activate',
            'plugin' => 'plugin',
            '_wpnonce' => $nonce
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
            $_COOKIE[$authCookieName] = $authCookieValue;
            // Simulate a link on "Activate" in the Plugins screen.
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_SERVER['REQUEST_URI'] = $requestUri;
            $_GET['plugin'] = $plugin;
            $_GET['action'] = 'activate';
            $_GET['_wpnonce'] = $nonce;
            include $pluginsFile;
        };
    }

    public function toDebugPluginActivationResult(): Closure
    {
        return function (Exited $exited): void {
            $id = $exited->getId();
            $exitCode = $exited->getExitCode();
            if ($exitCode === 0) {
                $message = "Plugin $id activation: OK";
            } else {
                $stdout = $exited->getStdout();
                $stderr = $exited->getStderr();
                $message = "Plugin $id activation: FAILED\n\tExit code: $exitCode\n\tSTDOUT: $stdout\n\tSTDERR: $stderr";
                $returnValue = $exited->getReturnValue();
                if ($returnValue instanceof Throwable) {
                    $message .= "\n\tError: {$returnValue->getMessage()}";
                }
            }
            ($this->debugFn)($message);
        };
    }
}
