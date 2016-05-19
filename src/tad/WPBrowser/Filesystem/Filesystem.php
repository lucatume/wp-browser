<?php

namespace tad\WPBrowser\Filesystem;


class Filesystem extends \Symfony\Component\Filesystem\Filesystem
{
    public function require_once($file)
    {
        require_once $file;
    }

    public function getUserHome()
    {
        // on Windows the POSIX library is not implemented so use environment variables
        if (function_exists('posix_getpwuid')) {
            $userInfo = posix_getpwuid(posix_getuid());
            $home_dir = $userInfo['dir'];
        } else if (getenv("HOMEPATH") !== false) {
            $home_dir = getenv("HOMEDRIVE") . getenv("HOMEPATH");
        } else {
            throw new \RuntimeException("posix_getpwuid does not exist. Check if POSIX library is enabled.", 1);
        }

        return $home_dir;
    }

    public function is_dir($filename)
    {
        return is_dir($filename);
    }

    public function is_writeable($filename)
    {
        return is_writeable($filename);
    }

    public function unlink($filename)
    {
        return unlink($filename);
    }

    public function file_exists($filename)
    {
        return file_exists($filename);
    }

    public function file_put_contents($file, $data)
    {
        return file_put_contents($file, $data);
    }

    public function file_get_contents($file)
    {
        return file_get_contents($file);
    }
}