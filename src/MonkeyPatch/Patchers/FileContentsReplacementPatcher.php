<?php

namespace lucatume\WPBrowser\MonkeyPatch\Patchers;

use lucatume\WPBrowser\MonkeyPatch\MonkeyPatchingException;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class FileContentsReplacementPatcher implements PatcherInterface
{
    /**
     * @var string
     */
    private $fileContents;
    public function __construct(string $fileContents)
    {
        $this->fileContents = $fileContents;
    }

    /**
     * @throws MonkeyPatchingException
     */
    public function patch(string $fileContents, string $pathname): array
    {
        $hash = md5($pathname . $fileContents) . '_' . md5($this->fileContents);
        $replacementFile = FS::getTmpSubDir('_monkeypatch') . '/' . $hash . '.php';

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
    public function stat(string $pathname)
    {
        return stat($pathname);
    }
}
