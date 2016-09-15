<?php

namespace tad\WPBrowser\Tests\Support;

function rrmdir($src)
{
    if (!file_exists($src)) {
        return;
    }

    $dir = opendir($src);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
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

function importDump($dumpFile, $dbName, $dbUser = 'root', $dbPass = 'root', $dbHost = 'localhost')
{
    $command = 'mysql -h' . $dbHost . ' -u' . $dbUser . ' -p' . $dbPass . ' ' . $dbName . ' < ' . $dumpFile;
    exec($command, $output, $status);

    return $status;
}
