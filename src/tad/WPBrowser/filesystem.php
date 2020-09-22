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
 *
 * @return bool Whether the directory, and all its contents, were correctly removed or not.
 */
function rrmdir($src)
{
    if (is_file($src) || is_link($src)) {
        if (! unlink($src)) {
            return false;
        }
    }

    if (! is_dir($src)) {
        return true;
    }

    $dir = opendir($src);

    if ($dir === false) {
        throw new \RuntimeException("Could not open dir {$dir}.");
    }

    while (false !== ( $file = readdir($dir) )) {
        if (( $file !== '.' ) && ( $file !== '..' )) {
            $full = $src . '/' . $file;
            if (is_dir($full)) {
                if (!rrmdir($full)) {
                    return false;
                }
            } else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);

    return true;
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
 * @return string|false The resolved, absolute path or `false` on failure to resolve the path.
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

    $root = (string)preg_replace('/^~/', $homeDir, $root);

    if (empty($path)) {
        return realpathish($root);
    }

    $path = (string)preg_replace('/^~/', $homeDir, $path);

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

    /** @var \SplFileInfo $file */
    foreach (new \FilesystemIterator($source, \FilesystemIterator::SKIP_DOTS) as $file) {
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
 * This function is just an alias of the `tad\WPBrowser\rrmdir` one.
 *
 * @param string $target The absolute path to a directory to remove.
 *
 * @return bool Whether the removal of the directory or file was completed or not.
 */
function recurseRemoveDir($target)
{
    return rrmdir($target);
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

    $resolvedRoot = resolvePath($root);

    if ($resolvedRoot === false) {
        return false;
    }

    if (! is_dir($resolvedRoot)) {
        $resolvedRoot = dirname($resolvedRoot);
    }

    $dir  = untrailslashit($resolvedRoot);
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

/**
 * Recursively Create a directory structure with files and sub-directories starting at a root path.
 *
 * @param string                            $pathname The path to the root directory, if not existing, it will be
 *                                                    recursively created.
 * @param string|array<string,array|string> $contents Either a directory structure to produce or the contents of a file
 *                                                    to create.
 * @param int                               $mode     The filemode that will be used to create each directory in the
 *                                                    directory tree.
 *
 * @return void This function does not return any value.
 *
 * @throws \RuntimeException If the creation of a directory or file fails.
 */
function mkdirp($pathname, $contents = [], $mode = 0777)
{
    if (is_array($contents)) {
        if (! is_dir($pathname) && ! mkdir($pathname, $mode, true) && ! is_dir($pathname)) {
            throw new \RuntimeException("Could not create directory {$pathname}");
        }
        foreach ($contents as $subPath => $subContents) {
            mkdirp(
                rtrim($pathname, '\\/') . '/' . ltrim($subPath, '\\/'),
                $subContents,
                $mode
            );
        }

        return;
    }

    if (! file_put_contents($pathname, $contents)) {
        throw new \RuntimeException("Could not put file contents in file {$pathname}");
    }
}
