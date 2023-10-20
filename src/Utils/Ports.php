<?php

namespace lucatume\WPBrowser\Utils;

class Ports
{
    public static function isPortOccupied(int $port): bool
    {
        $testPortCommand = DIRECTORY_SEPARATOR === '\\'
            ? "netstat -an | findstr /r \":$port.*LISTEN\""
            : "netstat -an | grep \"LISTEN \" | grep \".$port\"";
        $output = shell_exec($testPortCommand);
        return !empty($output);
    }
}
