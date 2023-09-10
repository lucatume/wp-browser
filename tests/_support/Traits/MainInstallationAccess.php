<?php

namespace lucatume\WPBrowser\Tests\Traits;

use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\Installation;
use RuntimeException;

trait MainInstallationAccess
{
    protected function copyOverContentFromTheMainInstallation(Installation $installation): void
    {
        $mainWPInstallationRootDir = Env::get('WORDPRESS_ROOT_DIR');
        foreach ([
                     'hello-dolly',
                     'akismet',
                     'woocommerce',
                 ] as $plugin) {
            if (!FS::recurseCopy($mainWPInstallationRootDir . '/wp-content/plugins/' . $plugin,
                $installation->getPluginsDir($plugin))) {
                throw new RuntimeException(sprintf('Could not copy plugin %s', $plugin));
            }
        }
        // Copy over theme from the main installation.
        if (!FS::recurseCopy($mainWPInstallationRootDir . '/wp-content/themes/twentytwenty',
            $installation->getThemesDir('twentytwenty'))) {
            throw new RuntimeException('Could not copy theme twentytwenty');
        }
    }
}
