<?php

namespace tad\WPBrowser\Filesystem;


class Filesystem extends \Symfony\Component\Filesystem\Filesystem
{
    public function requireOnce($file)
    {
        require_once $file;
    }

    public function getUserHome()
    {
        // on Windows the POSIX library is not implemented so use environment variables
        if ( function_exists('posix_getpwuid') ) {
            $userInfo = posix_getpwuid( posix_getuid() );
            $home_dir = $userInfo['dir'];
        } else if ( getenv("HOMEPATH") !== false ) {
            $home_dir = getenv("HOMEDRIVE") . getenv("HOMEPATH");
        } else {
            throw new \RuntimeException("posix_getpwuid does not exist. Check if POSIX library is enabled.", 1);
        }

        return $home_dir;
    }
}