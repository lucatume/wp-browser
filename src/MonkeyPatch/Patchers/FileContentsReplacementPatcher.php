<?php

namespace lucatume\WPBrowser\MonkeyPatch\Patchers;

use lucatume\WPBrowser\MonkeyPatch\MonkeyPatchingException;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\MonkeyPatch;

class FileContentsReplacementPatcher implements PatcherInterface
{
    public function __construct(private string $fileContents)
    {
    }

    /**
     * @throws MonkeyPatchingException
     */
    public function patch(string $fileContents, string $pathname, string $context = null): array
    {
        $replacementFile = MonkeyPatch::getReplacementFileName($pathname, $context ?? $this->fileContents);

        $isFile = is_file($replacementFile);
        if (!$isFile && !file_put_contents($replacementFile, $this->fileContents, LOCK_EX)) {
            throw new MonkeyPatchingException("Could not write replacement file: $replacementFile");
        }

        $replacementFileContents = $this->fileContents;

        return [$replacementFileContents, $replacementFile];
    }

    /**
     * @return array{
     *     dev: int,
     *     ino: int,
     *     mode: int,
     *     nlink: int,
     *     uid: int,
     *     gid: int,
     *     rdev: int,
     *     size: int,
     *     atime: int,
     *     mtime: int,
     *     ctime: int,
     *     blksize: int,
     *     blocks: int
     * }|false
     */
    public function stat(string $pathname): array|false
    {
        return stat($pathname);
    }
}
