<?php
/**
 * Functions related to the manipulation and interaction with the filesystem.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

use lucatume\WPBrowser\Utils\Filesystem;

/**
 * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::rrmdir` instead.
 */
function rrmdir(string $src): bool
{
    return Filesystem::rrmdir($src);
}

/**
 * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::homeDir` instead.
 */
function homeDir(string $path = ''): string
{
    return Filesystem::homeDir($path);
}

/**
 * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::resolvePath` instead.
 */
function resolvePath(string $path, string $root = null): bool|string
{
    return Filesystem::resolvePath($path, $root);
}

/**
 * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::untrailslashit` instead.
 */
function untrailslashit(string $path): string
{
    return Filesystem::untrailslashit($path);
}

/**
 * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::unleadslashit` instead.
 */
function unleadslashit(string $path): string
{
    return Filesystem::unleadslashit($path);
}

/**
 * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::recurseCopy` instead.
 */
function recurseCopy(string $source, string $destination): bool
{
    return Filesystem::recurseCopy($source, $destination);
}

/**
 * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::rrmdir` instead.
 */
function recurseRemoveDir(string $target): bool
{
    return Filesystem::rrmdir($target);
}

/**
 * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::findHereOrInParentrmdir` instead.
 */
function findHereOrInParent(string $path, string $root): bool|string
{
    return Filesystem::findHereOrInParent($path, $root);
}

/**
 * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::realpathish` instead.
 */
function realpathish(string $path): bool|string
{
    return Filesystem::realpathish($path);
}

/*
 * @deprecated Use `lucatume\WPBrowser\Utils\Filesystem::mkdirp` instead.
 */
function mkdirp(string $pathname, array|string $contents = [], int $mode = 0777): void
{
    Filesystem::mkdirp($pathname, $contents, $mode);
}
