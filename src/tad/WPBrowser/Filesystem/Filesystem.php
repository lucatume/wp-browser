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
        $userInfo = posix_getpwuid(posix_getuid());
        return $userInfo['dir'];
    }
}