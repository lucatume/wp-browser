<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequest;
use lucatume\WPBrowser\WordPress\InstallationException;
use WP_Theme;
use function switch_theme;
use function wp_get_theme;

class ThemeSwitchAction implements CodeExecutionActionInterface
{
    /**
     * @var \lucatume\WPBrowser\WordPress\FileRequests\FileRequest
     */
    private $request;

    public function __construct(
        FileRequest $request,
        string $wpRootDir,
        string $stylesheet,
        bool $multisite
    ) {
        $request->setTargetFile($wpRootDir . '/wp-load.php')
            ->runInFastMode($wpRootDir)
            ->defineConstant('MULTISITE', $multisite)
            ->addAfterLoadClosure(function () use ($stylesheet, $multisite) {
                return $this->switchTheme($stylesheet, $multisite);
            });
        $this->request = $request;
    }

    /**
     * @throws InstallationException
     */
    private function switchTheme(string $stylesheet, bool $multisite): void
    {
        // The `switch_theme` function will not complain about a missing theme: check it now.
        $theme = wp_get_theme($stylesheet);

        if (!($theme instanceof WP_Theme && $theme->exists() && !$theme->errors())) {
            $themeRealPath = realpath($stylesheet);

            if ($themeRealPath && is_dir($themeRealPath) && is_file($themeRealPath . '/style.css')) {
                $this->loadThemeFromFile($themeRealPath, $multisite);
                return;
            }

            $message = "Errors with theme $stylesheet.";
            if ($theme->errors()) {
                $message = implode(', ', $theme->errors()->get_error_messages());
            }

            throw new InstallationException($message);
        }

        if ($multisite) {
            WP_Theme::network_enable_theme($stylesheet);
        }

        switch_theme($stylesheet);
    }

    public function getClosure(): Closure
    {
        $request = $this->request;

        return static function () use ($request) {
            return $request->execute();
        };
    }

    private function loadThemeFromFile(string $themeRealPath, bool $multisite): void
    {
        include_once $themeRealPath . '/functions.php';
        $basename = basename($themeRealPath);
        update_option('template', $basename);
        update_option('stylesheet', $basename);
        do_action('after_setup_theme');
    }
}
