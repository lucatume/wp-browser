<?php
namespace tad\WPBrowser\Filesystem;


class Utils
{

    public static function findHereOrInParent($frag,
        $start,
        Filesystem $filesystem = null)
    {
        if (!is_string($frag)) {
            throw new  \InvalidArgumentException('Frag must be a string');
        }
        if (!is_string($start)) {
            throw new  \InvalidArgumentException('Start must be a string');
        }
        if (empty($filesystem)) {
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

        return $dir == '/' ? false : realpath($dir . DIRECTORY_SEPARATOR . $frag);
    }

    /**
     * Replaces the `~` symbol with the user home path.
     *
     * @param string $path
     * @return string The path with the `~` replaced with the user home path if any.
     */
    public static function homeify($path,
        Filesystem $filesystem = null)
    {
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
}