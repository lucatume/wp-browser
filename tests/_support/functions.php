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

function importDump($dumpFile, $dbName, $dbUser = 'root', $dbPass = 'root', $dbHost = 'localhost', &$output = null)
{
    $dumpFileContents = file_get_contents($dumpFile);
    if (empty($dumpFileContents)) {
        return -1;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s', $dbHost, $dbName);
    $db = new \PDO($dsn, $dbUser, $dbPass);

    $mysqlVersion = $db->getAttribute(\PDO::ATTR_CLIENT_VERSION);

    if (version_compare($mysqlVersion, '5.5.3', '<')) {
        $dumpFileContents = preg_replace('/CHARSET=(utf8[^\\s]*)/', 'CHARSET=utf8', $dumpFileContents);
        $dumpFileContents = preg_replace('/COLLATE=(utf8[^\\s]*)/', 'COLLATE=utf8_bin', $dumpFileContents);
    }

    return false !== $db->exec($dumpFileContents);
}
