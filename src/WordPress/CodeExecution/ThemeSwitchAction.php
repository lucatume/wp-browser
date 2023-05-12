<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequest;

class ThemeSwitchAction implements CodeExecutionActionInterface
{
    private FileRequest $request;

    public function __construct(
        FileRequest $request,
        string $wpRootDir,
        string $stylesheet,
        bool $multisite
    ) {
        $request->setTargetFile($wpRootDir . '/wp-load.php')
            ->defineConstant('MULTISITE', $multisite)
            ->addAfterLoadClosure(fn() => $this->switchTheme($stylesheet, $multisite));
        $this->request = $request;
    }

    private function switchTheme(string $stylesheet, bool $multisite): void
    {
        // The `switch_theme` function will not complain about a missing theme: check it now.
        $theme = \wp_get_theme($stylesheet);
        if (!($theme instanceof \WP_Theme && $theme->exists())) {
            throw new RuntimeException("Theme $stylesheet does not exist.");
        }

        if ($multisite) {
            \WP_Theme::network_enable_theme($stylesheet);
        }

        \switch_theme($stylesheet);
    }

    public function getClosure(): \Closure
    {
        $request = $this->request;

        return static function () use ($request): mixed {
            return $request->execute();
        };
    }
}
