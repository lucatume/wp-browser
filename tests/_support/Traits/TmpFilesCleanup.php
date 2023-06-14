<?php

namespace lucatume\WPBrowser\Tests\Traits;

use lucatume\WPBrowser\Utils\Filesystem;
use lucatume\WPBrowser\WordPress\Installation;

trait TmpFilesCleanup
{
    /**
     * @afterClass
     */
    public function removeTmpFiles(): void
    {
        codecept_debug('Removing tmp files ...');
        foreach (Filesystem::getCleanTmpFiles() as $file) {
            Filesystem::rrmdir($file);
            codecept_debug("Removed $file");
        }

        codecept_debug('Removing tmp Installations ...');
        foreach (Installation::getScaffoldedInstallations() as $dir) {
            Filesystem::rrmdir($dir);
            codecept_debug("Removed $dir");
        }
    }
}
