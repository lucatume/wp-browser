<?php

namespace lucatume\WPBrowser\Utils;

use lucatume\WPBrowser\MonkeyPatch\FileStreamWrapper;
use lucatume\WPBrowser\MonkeyPatch\Patchers\FileContentsReplacementPatcher;
use lucatume\WPBrowser\MonkeyPatch\Patchers\FileReplacementPatcher;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class MonkeyPatch
{

    public static function redirectFileToFile(
        string $fromFile,
        string $toFile,
        bool $redirectOpenedPath = true,
        string $context = null
    ): void {
        FileStreamWrapper::setPatcherForFile(
            $fromFile,
            new FileReplacementPatcher($toFile),
            $redirectOpenedPath,
            $context
        );
    }

    public static function dudFile(): string
    {
        return dirname(__DIR__) . '/MonkeyPatch/dud-file.php';
    }

    public static function redirectFileContents(
        string $fromFile,
        string $fileContents,
        bool $redirectOpenedPath = true,
        string $context = null
    ): void {
        FileStreamWrapper::setPatcherForFile(
            $fromFile,
            new FileContentsReplacementPatcher($fileContents),
            $redirectOpenedPath,
            $context
        );
    }

    public static function getReplacementFileName(string $pathname, string $context): string
    {
        $mtime = (string)filemtime($pathname);
        $hash = md5($pathname . $mtime . $context);
        return FS::getTmpSubDir('_monkeypatch') . "/{$hash}.php";
    }

    public static function getCachePath(): string
    {
        return FS::getTmpSubDir('_monkeypatch');
    }
}
