<?php
namespace lucatume\WPBrowser\Utils;

class ErrorHandling
{
    public static function throwWarnings(): void
    {
        $errorHandler = static function (int $errno, string $errstr): void {
            throw new \Error($errstr, $errno);
        };
        $errorLevels = E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING | E_NOTICE | E_USER_NOTICE;
        set_error_handler($errorHandler, $errorLevels);
    }
}
