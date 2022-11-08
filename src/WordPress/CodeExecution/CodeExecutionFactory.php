<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;

class CodeExecutionFactory
{
    private string $wpRootDir;

    public function __construct(string $wpRootDir)
    {
        $this->wpRootDir = rtrim($wpRootDir, '\\/');
    }

    public function toCheckIfWpIsInstalled(bool $isMultisite): Closure
    {
        $wrappedCode = new WordPressClosure($this->wpRootDir, static function () use ($isMultisite): bool {
            return is_blog_installed() && (!$isMultisite || is_multisite());
        });

        return static function () use ($wrappedCode) {
            return $wrappedCode->execute();
        };
    }
}
