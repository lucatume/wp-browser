<?php

namespace lucatume\WPBrowser\MonkeyPatch\Patchers;

interface PatcherInterface
{
    /**
     * @return array{string, string}
     */
    public function patch(string $fileContents, string $pathname, ?string $context = null): array;

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
    public function stat(string $pathname);
}
