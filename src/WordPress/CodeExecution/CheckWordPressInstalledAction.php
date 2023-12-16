<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequest;
use lucatume\WPBrowser\WordPress\WPConfigFile;

class CheckWordPressInstalledAction implements CodeExecutionActionInterface
{
    private FileRequest $request;

    public function __construct(FileRequest $request, string $wpRootDir, bool $multisite)
    {
        $request->setTargetFile($wpRootDir . '/wp-load.php')
            ->runInFastMode($wpRootDir)
            ->addAfterLoadClosure(fn(): bool => $this->isBlogInstalled($multisite));
        $this->request = $request;
    }

    private function isBlogInstalled(bool $multisite): bool
    {
        return is_blog_installed() && (!$multisite || is_multisite());
    }

    public function getClosure(): Closure
    {
        $request = $this->request;

        return static function () use ($request): mixed {
            return $request->execute();
        };
    }
}
