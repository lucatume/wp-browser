<?php
/**
 * Provides filesystem related utils.
 *
 * @package tad\WPBrowser\Filesystem
 */
namespace tad\WPBrowser\Filesystem;

/**
 * Class Utils
 *
 *
 * @package tad\WPBrowser\Filesystem
 */
class Utils
{
    /**
     * Finds a path fragment, the partial path to a directory or file, in the current directory or in a parent one.
     *
     * @param string          $frag       The path fragment to find.
     * @param string          $start      The starting search path.
     * @param Filesystem|null $filesystem An instance of the Filesystem abstraction object.
     * @return string|false The full path to the found result, or `false` to indicate the fragment was not found.
     */
    public static function findHereOrInParent(
        $frag,
        $start,
        Filesystem $filesystem = null
    ) {
        if (!is_string($frag)) {
            throw new  \InvalidArgumentException('Frag must be a string');
        }

        if (!is_string($start)) {
            throw new  \InvalidArgumentException('Start must be a string');
        }

        if ($filesystem === null) {
            $filesystem = new Filesystem();
        }

        $start = self::homeify($start, $filesystem);

        if (!file_exists($start)) {
            throw new \InvalidArgumentException('Start must be a valid path to a file or directory');
        }

        if (!is_dir($start)) {
            $start = dirname($start);
        }

        $dir = self::untrailslashit($start);
        $frag = self::unleadslashit($frag);

        while (!file_exists($dir . DIRECTORY_SEPARATOR . $frag) && '/' !== $dir) {
            $dir = dirname($dir);
        }

        return $dir === '/' ? false : realpath($dir . DIRECTORY_SEPARATOR . $frag);
    }

    /**
     * Replaces the `~` symbol with the user home path.
     *
     * @param string $path
     * @return string The path with the `~` replaced with the user home path if any.
     */
    public static function homeify(
        $path,
        Filesystem $filesystem = null
    ) {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Paht must be a string');
        }
        if (empty($filesystem)) {
            $filesystem = new Filesystem();
        }
        $userHome = $filesystem->getUserHome();
        if (!(empty($userHome) && false !== strpos($path, '~'))) {
            $path = str_replace('~', $userHome, $path);
        }
        return $path;
    }

    public static function untrailslashit($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Path must be a string');
        }
        return $path !== DIRECTORY_SEPARATOR ? rtrim($path, DIRECTORY_SEPARATOR) : $path;
    }

    public static function unleadslashit($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Path must be a string');
        }
        return $path !== DIRECTORY_SEPARATOR ? ltrim($path, DIRECTORY_SEPARATOR) : $path;
    }

    /**
     * Recursively copies a source to a destination.
     *
     * @param string $source      The absolute path to the source.
     * @param string $destination The absolute path to the destination.
     *
     * @return bool Whether the recurse directory of file copy was successful or not.
     */
    public static function recurseCopy($source, $destination)
    {
        if (!is_dir($destination) && !mkdir($destination) && !is_dir($destination)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $destination));
        }

        $iterator = new \FilesystemIterator( $source, \FilesystemIterator::SKIP_DOTS );

        foreach ($iterator as $file){
            if ($file->isDir()) {
                if(!static::recurseCopy($file->getPathname(), $destination . '/' . $file->getBasename())){
                    return false;
                }
            } elseif(!copy($file->getPathname(), $destination . '/' . $file->getBasename())){
                return false;
            }

        }

        return true;
    }

    /**
     * Recursively deletes a target directory.
     *
     * @param string $target The absolute path to a directory to remove.
     * @return bool Whether the removal of the directory or file was completed or not.
     */
    public static function recurseRemoveDir($target)
    {
        if (!file_exists($target)) {
            return true;
        }

        $iterator = new \FilesystemIterator($target,\FilesystemIterator::SKIP_DOTS);
        foreach ($iterator as $file){
            if (is_dir($file->getPathname())) {
                if (!static::recurseRemoveDir($file->getPathname())) {
                    return false;
                }
            } elseif(!unlink($file->getPathname())){
                return false;
            }
        }

        return true;
    }
}
