<?php
/**
 * Functions related to the manipulation and interaction with the filesystem.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

/**
 * Recursively removes a directory and all its content.
 *
 * Differently from the `recurseRemoveDir` function, this one wil not stop on error.
 *
 * @param string $src The absolute path to the directory to remove.
 */
function rrmdir($src)
{
    if (! file_exists($src)) {
        return;
    }

    $dir = opendir($src);
    while (false !== ( $file = readdir($dir) )) {
        if (( $file !== '.' ) && ( $file !== '..' )) {
            $full = $src . '/' . $file;
            if (is_dir($full)) {
                rrmdir($full);
            } else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);
}

/**
 * Returns the absolute path to the current HOME directory.
 *
 * @param string $path An optional path to append to the HOME directory path.
 *
 * @return string The HOME directory resolved path.
 */
function homeDir($path = '')
{
    static $home;

    if ($home === null) {
        foreach ([ 'HOME', 'HOMEDRIVE', 'HOMEPATH' ] as $homeCandidate) {
            if (isset($_SERVER[ $homeCandidate ])) {
                $home = untrailslashit($_SERVER[ $homeCandidate ]);
                break;
            }
            if (isset($_ENV[ $homeCandidate ])) {
                $home = untrailslashit($_ENV[ $homeCandidate ]);
                break;
            }
        }
    }

    if (! empty($home)) {
        return empty($path) ? $home : $home . '/' . unleadslashit($path);
    }

    throw new \RuntimeException('Could not resolve the HOME directory path.');
}

/**
 * Resolves a path from a specified root to an absolute path.
 *
 * @param string      $path The path to resolve from the root.
 * @param string|null $root Either the absolute path to resolve the path from, or `null` to use the current working
 *                          directory.
 *
 * @return string The resolved, absolute path.
 *
 * @throws \InvalidArgumentException If the root or path cannot be resolved.
 */
function resolvePath($path, $root = null)
{
    $root = $root ?: getcwd();

    if (empty($root) || !is_dir($root)) {
        throw new \InvalidArgumentException('Root must be specified.');
    }

    $homeDir = homeDir();

    $root = preg_replace('/^~/', $homeDir, $root);

    if (empty($path)) {
        return realpathish($root);
    }

    $path = preg_replace('/^~/', $homeDir, $path);

    if (file_exists($path)) {
        return realpathish($path);
    }

    $resolved = realpathish($root . '/' . $path);

    if ($resolved === false) {
        throw new \InvalidArgumentException("Cannot resolve the path '{$path}' from root '{$root}'");
    }

    return $resolved;
}

/**
 * Removes the trailing slash from a path.
 *
 * @param string $path The path to remove the trailing slash from.
 *
 * @return string The path, with trailing slashes removed.
 */
function untrailslashit($path)
{
    return $path !== '/' ? rtrim($path, '\\/') : $path;
}

/**
 * Removes the leading slash from a path.
 *
 * @param string $path The path to remove the leading slash from.
 *
 * @return string The path, with leading slashes removed.
 */
function unleadslashit($path)
{
    return $path !== '/' ? ltrim($path, '\\/') : $path;
}

/**
 * Recursively copies a source to a destination.
 *
 * @param string $source      The absolute path to the source.
 * @param string $destination The absolute path to the destination.
 *
 * @return bool Whether the recurse directory of file copy was successful or not.
 */
function recurseCopy($source, $destination)
{
    if (! is_dir($destination) && ! mkdir($destination) && ! is_dir($destination)) {
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $destination));
    }

    $iterator = new \FilesystemIterator($source, \FilesystemIterator::SKIP_DOTS);

    foreach ($iterator as $file) {
        if ($file->isDir()) {
            if (! recurseCopy($file->getPathname(), $destination . '/' . $file->getBasename())) {
                return false;
            }
        } elseif (! copy($file->getPathname(), $destination . '/' . $file->getBasename())) {
            return false;
        }
    }

    return true;
}


/**
 * Recursively deletes a target directory.
 *
 * @param string $target The absolute path to a directory to remove.
 *
 * @return bool Whether the removal of the directory or file was completed or not.
 */
function recurseRemoveDir($target)
{
    if (! file_exists($target)) {
        return true;
    }

    $iterator = new \FilesystemIterator($target, \FilesystemIterator::SKIP_DOTS);
    foreach ($iterator as $file) {
        if (is_dir($file->getPathname())) {
            if (! recurseRemoveDir($file->getPathname())) {
                return false;
            }
        } elseif (! unlink($file->getPathname())) {
            return false;
        }
    }

    return true;
}

/**
 * Finds a path fragment, the partial path to a directory or file, in the current directory or in a parent one.
 *
 * @param string $path The path fragment to find.
 * @param string $root The starting search path.
 *
 * @return string|false The full path to the found result, or `false` to indicate the fragment was not found.
 */
function findHereOrInParent($path, $root)
{
    if (file_exists($path)) {
        return realpathish($path);
    }

    $root = resolvePath($root);

    if (! is_dir($root)) {
        $root = dirname($root);
    }

    $dir  = untrailslashit($root);
    $path = unleadslashit($path);

    while (! file_exists($dir . '/' . $path) && '/' !== $dir) {
        $dir = dirname($dir);
    }

    return $dir === '/' ? false : resolvePath($path, $dir);
}

/**
 * Realpath, withs support for virtual file systems.
 *
 * @param string $path The path to resolve.
 *
 * @return false|string The realpath, or `false` if it could not be resolved.
 */
function realpathish($path)
{
    $realpath = realpath($path);

    if ($realpath) {
        return $realpath;
    }

    return file_exists($path) ? $path : false;
}
