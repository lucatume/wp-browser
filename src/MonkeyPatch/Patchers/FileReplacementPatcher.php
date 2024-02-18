<?php

namespace lucatume\WPBrowser\MonkeyPatch\Patchers;

use lucatume\WPBrowser\MonkeyPatch\MonkeyPatchingException;

class FileReplacementPatcher implements PatcherInterface
{
    public function __construct(private string $replacementFile)
    {
    }

    /**
     * @throws MonkeyPatchingException
     */
    public function patch(string $fileContents, string $pathname, string $context = null): array
    {
        $replacementFileContents = file_get_contents($this->replacementFile);

        if ($replacementFileContents === false) {
            throw new MonkeyPatchingException("Could not read replacement file: {$this->replacementFile}");
        }

        return [$replacementFileContents, $this->replacementFile];
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
        return stat($this->replacementFile);
    }
}
