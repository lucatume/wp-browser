<?php

namespace lucatume\WPBrowser\WordPress;

use Closure;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequestFactory;

class RequestClosureFactory
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

        return static function () use ($request) {
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

        return static function () use ($request) {
            $request->execute();
        };
    }
}
