<?php

namespace lucatume\WPBrowser\Utils;

use lucatume\WPBrowser\MonkeyPatch\FileStreamWrapper;
use lucatume\WPBrowser\MonkeyPatch\Patchers\FileContentsReplacementPatcher;
use lucatume\WPBrowser\MonkeyPatch\Patchers\FileReplacementPatcher;

class MonkeyPatch
{

    public static function redirectFileToFile(string $fromFile, string $toFile): void
    {
        FileStreamWrapper::setPatcherForFile($fromFile, new FileReplacementPatcher($toFile));
    }

    public static function dudFile(): string
    {
        return dirname(__DIR__) . '/MonkeyPatch/dud-file.php';
    }

    public static function redirectFileContents(string $fromFile, string $fileContents):void
    {
        FileStreamWrapper::setPatcherForFile($fromFile, new FileContentsReplacementPatcher($fileContents));
    }
}
