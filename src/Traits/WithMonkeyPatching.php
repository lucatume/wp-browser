<?php
/**
 * Provides methods to monkey patch code.
 *
 * @package lucatume\WPBrowser\Traits;
 */

namespace lucatume\WPBrowser\Traits;

use lucatume\WPBrowser\Streams\MonkeyPatcher;

/**
 * Class WithMonkeyPatching.
 *
 * @package lucatume\WPBrowser\Traits;
 */
trait WithMonkeyPatching
{
    /**
     * Patches a file at most once.
     *
     * @since TBD
     *
     * @param string $file            The path to the file to patch.
     * @param string $newFileContents The file contents, that should replace
     *                                the ones in the original file.
     *
     * @return void
     */
    private function patchFileOnce($file, $newFileContents)
    {
        $patcher = static function () use ($newFileContents, &$patcher) {
            MonkeyPatcher::removePatcher($patcher);

            return $newFileContents;
        };

        MonkeyPatcher::atchFileWith($file, $patcher);
    }
}
