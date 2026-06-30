<?php

namespace lucatume\WPBrowser\Tests\Traits;

use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\Installation;
use lucatume\WPBrowser\WordPress\Source;
use RuntimeException;

/**
 * Test-only drop-in replacement for {@see Installation::scaffold()}.
 *
 * Uses {@see FS::cowCopy()} (which, on macOS, issues a single clonefile(2) syscall via FFI)
 * instead of the production recurseCopy path, saving ~1s per scaffold on APFS. The returned
 * Installation has the same Scaffolded state as the production method because Installation's
 * state detection is content-based: a directory containing wp-load.php and no wp-config.php
 * is detected as Scaffolded.
 *
 * The production Installation::scaffold() also appends the wpRoot to a private static
 * $scaffoldedInstallations array for later external cleanup. Tests using TmpFilesCleanup
 * clean up their tmp dirs directly, so skipping that registration is safe.
 */
trait FastScaffold
{
    protected function fastScaffold(string $wpRoot, string $version = 'latest'): Installation
    {
        if ($version === 'latest') {
            // Stopgap (#804): honor the WORDPRESS_VERSION pin used by CI so tests that scaffold
            // `latest` avoid WordPress releases that break the installer (e.g. 7.0's multisite install).
            $pinned = getenv('WORDPRESS_VERSION');
            if (is_string($pinned) && $pinned !== '' && $pinned !== 'latest') {
                $version = $pinned;
            }
        }

        if (!FS::cowCopy(Source::getForVersion($version), $wpRoot)) {
            throw new RuntimeException(sprintf('FastScaffold: could not clone WordPress source into "%s"', $wpRoot));
        }

        return new Installation($wpRoot);
    }
}
