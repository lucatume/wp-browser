<?php

namespace tad\WPBrowser\Tests\Support;

function importDump($dumpFile, $dbName, $dbUser = 'root', $dbPass = 'root', $dbHost = 'localhost')
{
    if (strpos($dbHost, ':') >0) {
        list($dbHost, $dbPort) = explode(':', $dbHost);
        $dbHost = sprintf('%s -P %d', $dbHost, $dbPort);
    }

    $commandTemplate = 'mysql -h %s -u %s %s %s < %s';
    $dbPassEntry = $dbPass ? '-p' . $dbPass : '';

    $sql = file_get_contents($dumpFile);

    if (false === $sql) {
        return false;
    }

    $command = sprintf($commandTemplate, $dbHost, $dbUser, $dbPassEntry, $dbName, $dumpFile);
    exec($command, $output, $status);

    return $status === false;
}


/**
* Normalizes a string new line bytecode for comparison
* through Unix and Windows environments.
*
* @see https://stackoverflow.com/a/7836692/2056484
*/
function normalizeNewLine($str)
{
    return preg_replace('~(*BSR_ANYCRLF)\R~', "\n", $str);
}
