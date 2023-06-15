<?php

declare(strict_types=1);

/**
 * Provides methods to manipulate and interact with the filesystem.
 *
 * @package lucatume\WPBrowser\Utils;
 */

namespace lucatume\WPBrowser\Utils;

use Exception;
use InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;

/**
 * Class Filesystem.
 *
 * @package lucatume\WPBrowser\Utils;
 */
class Filesystem
{
    /**
     * @var array<string>
     */
    private static array $tmpFiles = [];

    /**
     * Recursively removes a directory and all its content.
     *
     * @param string $src The absolute path to the directory to remove.
     *
     * @return bool Whether the directory, and all its contents, were correctly removed or not.
     */
    public static function rrmdir(string $src): bool
    {
        if (is_file($src) || is_link($src)) {
            if (!unlink($src)) {
                return false;
            }
        }

        if (!is_dir($src)) {
            return true;
        }

        $dir = opendir($src);

        if ($dir === false) {
            throw new RuntimeException("Could not open dir {$dir}.");
        }

        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                $full = $src . '/' . $file;
                if (is_dir($full)) {
                    if (!self::rrmdir($full)) {
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
    public static function homeDir(string $path = ''): string
    {
        foreach (['HOME', 'HOMEDRIVE', 'HOMEPATH'] as $homeCandidate) {
            if (isset($_SERVER[$homeCandidate])) {
                $home = self::untrailslashit($_SERVER[$homeCandidate]);
                break;
            }
            if (isset($_ENV[$homeCandidate])) {
                $home = self::untrailslashit($_ENV[$homeCandidate]);
                break;
            }
        }

        if (!empty($home)) {
            return empty($path) ? $home : $home . '/' . self::unleadslashit($path);
        }

        throw new RuntimeException('Could not resolve the HOME directory path.');
    }

    /**
     * Resolves a path from a specified root to an absolute path.
     *
     * @param string $path The path to resolve from the root.
     * @param string|null $root Either the absolute path to resolve the path from, or `null` to use the current working
     *                          directory.
     *
     * @return string|false The resolved, absolute path or `false` on failure to resolve the path.
     *
     * @throws InvalidArgumentException If the root or path cannot be resolved.
     */
    public static function resolvePath(string $path, string $root = null): bool|string
    {
        $root = $root ?? getcwd();

        if (empty($root) || !is_dir($root)) {
            throw new InvalidArgumentException('Root must be specified.');
        }

        $homeDir = self::homeDir();

        $root = (string)str_replace(['~', '\ '], [$homeDir, ' '], $root);

        if (empty($path)) {
            return self::realpath($root);
        }

        $path = (string)str_replace(['~', '\ '], [$homeDir, ' '], $path);

        if (file_exists($path)) {
            return self::realpath($path);
        }

        $resolved = self::realpath($root . '/' . $path);

        if ($resolved === false) {
            throw new InvalidArgumentException("Cannot resolve the path '{$path}' from root '{$root}'");
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
    public static function untrailslashit(string $path): string
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
    public static function unleadslashit(string $path): string
    {
        return $path !== '/' ? ltrim($path, '\\/') : $path;
    }

    /**
     * Recursively copies a source to a destination.
     *
     * @param string $source The absolute path to the source.
     * @param string $destination The absolute path to the destination.
     *
     * @return bool Whether the recurse directory of file copy was successful or not.
     */
    public static function recurseCopy(string $source, string $destination): bool
    {
        if (!is_dir($destination) && !mkdir($destination) && !is_dir($destination)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $destination));
        }

        $resolvedSource = self::resolvePath($source);

        if ($resolvedSource === false) {
            return false;
        }

        $resolvedDestination = self::resolvePath($destination);

        if ($resolvedDestination === false) {
            return false;
        }

        $escapedSource = escapeshellarg($resolvedSource);
        $escapedDestination = escapeshellarg($resolvedDestination);
        if (DIRECTORY_SEPARATOR === '\\') {
            $command = "xcopy /E /I /Y $escapedSource $escapedDestination";
        } else {
            if (is_dir($resolvedSource)) {
                $resolvedSource = rtrim($resolvedSource, '\\/') . '/.';
                $resolvedDestination = rtrim($resolvedDestination, '\\/') . '/';
            }
            $escapedSource = escapeshellarg($resolvedSource);
            $escapedDestination = escapeshellarg($resolvedDestination);
            $command = "cp -R -u $escapedSource $escapedDestination";
        }

        try {
            exec($command, $output, $exitCode);
        } catch (Exception $e) {
            $exitCode = $e->getCode();
            $output = $e->getMessage();
        }

        if ($exitCode !== 0) {
            codecept_debug("Recursive copy failed with exit code $exitCode and message: " .
                (is_string($output) ? $output : implode(PHP_EOL, $output)));
            return false;
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
    public static function findHereOrInParent(string $path, string $root): bool|string
    {
        if (file_exists($path)) {
            return self::realpath($path);
        }

        $resolvedRoot = self::resolvePath($root);

        if ($resolvedRoot === false) {
            return false;
        }

        if (!is_dir($resolvedRoot)) {
            $resolvedRoot = dirname($resolvedRoot);
        }

        $dir = self::untrailslashit($resolvedRoot);
        $path = self::unleadslashit($path);

        while (!file_exists($dir . '/' . $path) && '/' !== $dir) {
            $dir = dirname($dir);
        }

        return $dir === '/' ? false : self::resolvePath($path, $dir);
    }

    /**
     * Realpath, withs support for virtual file systems.
     *
     * @param string $path The path to resolve.
     *
     * @return false|string The realpath, or `false` if it could not be resolved.
     */
    public static function realpath(string $path): bool|string
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
     * @param string $pathname The path to the root directory, if not existing, it will be
     *                                                    recursively created.
     * @param string|array<string,string|array<string,mixed>> $contents Either a directory structure to produce or the
     *     contents of a file to create.
     * @param int $mode The filemode that will be used to create each directory in
     *                                                    the
     *                                                    directory tree.
     *
     * @return string The path to the created directory.
     *
     * @throws RuntimeException If the creation of a directory or file fails.
     */
    public static function mkdirp(string $pathname, array|string $contents = [], int $mode = 0777): string
    {
        if (is_array($contents)) {
            if (!is_dir($pathname) && !mkdir($pathname, $mode, true) && !is_dir($pathname)) {
                throw new RuntimeException("Could not create directory {$pathname}");
            }
            foreach ($contents as $subPath => $subContents) {
                if (!(is_array($subContents) || is_string($subContents))) {
                    throw new RuntimeException("Invalid contents for path {$subPath}");
                }
                /** @var array<string, array<string, mixed>|string>|string $subContents */
                self::mkdirp(
                    rtrim($pathname, '\\/') . '/' . ltrim((string)$subPath, '\\/'),
                    $subContents,
                    $mode
                );
            }

            return $pathname;
        }

        if (empty($contents)) {
            // If the file contents are empty, then just touch the file.
            touch($pathname);
        } elseif (!file_put_contents($pathname, $contents)) {
            throw new RuntimeException("Could not put file contents in file {$pathname}");
        }

        return $pathname;
    }

    /**
     * @param array<string,string|array<mixed>> $contents
     */
    public static function tmpDir(
        string $prefix = '',
        array $contents = [],
        int $mode = 0777,
        string $tmpRootDir = null
    ): string {
        if ($tmpRootDir === null) {
            $tmpRootDir = Env::get('TEST_TMP_ROOT_DIR') ?? codecept_output_dir('tmp');
            if (!is_dir($tmpRootDir)) {
                $tmpRootDir = self::mkdirp($tmpRootDir, [], 0777);
            }
            $tmpRootDir = self::realpath($tmpRootDir) ?: $tmpRootDir;
        }
        $dir = self::mkdirp(
            $tmpRootDir . DIRECTORY_SEPARATOR . $prefix . md5(microtime()),
            $contents,
            $mode
        );

        self::$tmpFiles[] = $dir;

        return $dir;
    }

    public static function relativePath(string $from, string $to, string $separator = DIRECTORY_SEPARATOR): string
    {
        if ($from === '') {
            return $to;
        }

        if ($to === '') {
            return '';
        }

        if ($separator === '') {
            $separator = DIRECTORY_SEPARATOR;
        }

        $fromRealPath = self::realpath($from) ?: $from;
        $toRealPath = self::realpath($to) ?: $to;
        $from = str_replace(['/', '\\'], $separator, $fromRealPath);
        $to = str_replace(['/', '\\'], $separator, $toRealPath);

        $fromParts = explode($separator, rtrim($from, $separator));
        $toParts = explode($separator, rtrim($to, $separator));
        while (count($fromParts) && count($toParts) && ($fromParts[0] === $toParts[0])) {
            array_shift($fromParts);
            array_shift($toParts);
        }

        return str_repeat('..' . $separator, count($fromParts)) . implode($separator, $toParts);
    }

    /**
     * @param array<string,string|array<mixed>> $contents
     */
    public static function getTmpSubDir(string $dirname, array $contents = [], int $mode = 0777): string
    {
        $pathname = codecept_output_dir('tmp/' . $dirname);

        if (is_dir($pathname)) {
            return $pathname;
        }

        return self::mkdirp(
            $pathname,
            $contents,
            $mode
        );
    }

    /**
     * @return array<string>
     */
    public static function getCleanTmpFiles(): array
    {
        $files = self::$tmpFiles;
        self::$tmpFiles = [];

        return $files;
    }
}
