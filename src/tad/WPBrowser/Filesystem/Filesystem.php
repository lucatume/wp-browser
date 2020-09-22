<?php
/**
 * Proxy to filesystem operations.
 *
 * @package tad\WPBrowser\Filesystem
 */

namespace tad\WPBrowser\Filesystem;

/**
 * Class Filesystem
 *
 * @package tad\WPBrowser\Filesystem
 */
class Filesystem extends \Symfony\Component\Filesystem\Filesystem
{

    /**
     * Proxy for `require_once` function.
     *
     * @param string $file The file to require.
     *
     * @return void
     */
    public function requireOnce($file)
    {
        require_once $file;
    }

    /**
     * Returns a user home directory.
     *
     * @return string The path to the user $HOME directory.
     *
     * @throws \RuntimeException If the user $HOME directory cannot be located.
     */
    public function getUserHome()
    {
        // on Windows the POSIX library is not implemented so use environment variables
        if (function_exists('posix_getpwuid')) {
            $userInfo = posix_getpwuid(posix_getuid());
            $home_dir = $userInfo['dir'];
        } elseif (getenv("HOMEPATH") !== false) {
            $home_dir = getenv("HOMEDRIVE") . getenv("HOMEPATH");
        } else {
            throw new \RuntimeException("posix_getpwuid does not exist. Check if POSIX library is enabled.", 1);
        }

        return $home_dir;
    }

    /**
     * Proxy to the `is_dir` function.
     *
     * @param string $filename The file to check.
     *
     * @return bool Whether a file is a dir or not.
     */
    public function is_dir($filename)
    {
        return is_dir($filename);
    }

    /**
     * Proxy to the `is_writeable` function.
     *
     * @param string $filename The file to check.
     *
     * @return bool Whether the file is writeable or not.
     */
    public function is_writeable($filename)
    {
        return is_writeable($filename);
    }

    /**
     * Proxy to the `unlink` function.
     *
     * @param string $filename The file to remove.
     *
     * @return bool Whether the file was correctly removed or not.
     */
    public function unlink($filename)
    {
        return unlink($filename);
    }

    /**
     * Proxy to the `file_exists` function.
     *
     * @param string $filename The file to check.
     *
     * @return bool Whether the file exists or not.
     */
    public function file_exists($filename)
    {
        return file_exists($filename);
    }

    /**
     * Proxy to the `file_put_contents` function.
     *
     * @param string $file The contents to write to the file.
     * @param mixed  $data The data to write to file.
     *
     * @return false|int Either the written bytes or `false` on failure.
     */
    public function file_put_contents($file, $data)
    {
        return file_put_contents($file, $data);
    }

    /**
     * Proxy to the `file_get_contents` function.
     *
     * @param string $file The path to the file to get.
     *
     * @return false|string The file contents or `false` on failure.
     */
    public function file_get_contents($file)
    {
        return file_get_contents($file);
    }

    /**
     * Proxy to the `is_readable` function.
     *
     * @param string $filename The file to check.
     *
     * @return bool Whether the file is readable or not.
     */
    public function is_readable($filename)
    {
        return is_readable($filename);
    }
}
