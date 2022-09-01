<?php
namespace lucatume\WPBrowser\Utils;

class Zip
{

    public static function extractTo(string $zipFile, string $destination): string
    {
        $zip = new \ZipArchive();

        if ($zip->open($zipFile) !== true) {
            throw new \RuntimeException("Could not open {$zipFile}.");
        }

        codecept_debug('Extractiing ');

        if ($zip->extractTo($destination) === false) {
            throw new \RuntimeException("Could not extract {$zipFile} to {$destination}.");
        }

        return $destination;
    }
}
