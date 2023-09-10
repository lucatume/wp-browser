<?php

namespace lucatume\WPBrowser\Tests\Traits;

use lucatume\WPBrowser\Utils\Filesystem;
use lucatume\WPBrowser\WordPress\Installation;

trait TmpFilesCleanup
{
    /**
     * @afterClass
     */
    public function tmpCleanup(string $context = ''): void
    {
        $context = $context ? ' ' . $context : '';
        codecept_debug("Removing tmp files$context ...");
        foreach (Filesystem::getCleanTmpFiles() as $file) {
            Filesystem::rrmdir($file);
            codecept_debug("Removed $file");
        }

        codecept_debug("Removing tmp Installations$context ...");
        foreach (Installation::getCleanScaffoldedInstallations() as $dir) {
            Filesystem::rrmdir($dir);
            codecept_debug("Removed $dir");
        }
    }

    /**
     * @after
     */
    public function tmpCleanupAfterTest(): void
    {
        if (property_exists($this, 'cleanupTmpAfterTest') && !$this->cleanupTmpAfterTest) {
            return;
        }
        $this->tmpCleanup('after test');
    }
}
