<?php

namespace lucatume\WPBrowser\Utils;

use RuntimeException;
use ZipArchive;

class Zip
{

    public static function extractTo(string $zipFile, string $destination): string
    {
        $zip = new ZipArchive();

        if ($zip->open($zipFile) !== true) {
            throw new RuntimeException("Could not open {$zipFile}.");
        }

        codecept_debug(sprintf("Extracting %s to %s ... ", $zipFile, $destination));

        if ($zip->extractTo($destination) === false) {
            throw new RuntimeException("Could not extract {$zipFile} to {$destination}.");
        }

        return $destination;
    }

    public static function extractFile(string $zipFile, string $filename, string $destinationFileName): string
    {
        $zip = new ZipArchive();

        if ($zip->open($zipFile) !== true) {
            throw new RuntimeException("Could not open {$zipFile}.");
        }

        if (($fileIndex = $zip->locateName($filename, ZipArchive::FL_NODIR)) === false) {
            throw new RuntimeException("Could not locate {$filename} in {$zipFile}.");
        }

        if (($name = $zip->getNameIndex($fileIndex)) === false) {
            throw new RuntimeException("Could not get name for {$fileIndex} in {$zipFile}.");
        }

        if (!copy("zip://{$zipFile}#{$name}", $destinationFileName)) {
            throw new RuntimeException("Could not copy {$name} from {$zipFile} to {$destinationFileName}.");
        }

        return $destinationFileName;
    }
}
