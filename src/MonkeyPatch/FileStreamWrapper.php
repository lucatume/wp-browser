<?php

namespace lucatume\WPBrowser\MonkeyPatch;

use ErrorException;
use Exception;
use lucatume\WPBrowser\MonkeyPatch\Patchers\PatcherInterface;
use lucatume\WPBrowser\Utils\Filesystem as FS;

class FileStreamWrapper
{
    protected static bool $isRegistered = false;

    /**
     * @var array<string,PatcherInterface>
     */
    private static array $fileToPatcherMap = [];

    /**
     * @var resource|null
     */
    public $context;

    /**
     * @var resource
     */
    private $fileResource;

    public static function setPatcherForFile(string $file, PatcherInterface $patcher): void
    {
        $fromFilePath = FS::realpath($file) ?: $file;
        self::$fileToPatcherMap[$fromFilePath] = $patcher;
        self::register();
    }

    public static function register(): bool
    {
        if (!static::$isRegistered) {
            static::$isRegistered = stream_wrapper_unregister('file')
                && stream_wrapper_register('file', __CLASS__);
        }

        return static::$isRegistered;
    }

    public static function unregister(): bool
    {
        if (static::$isRegistered) {
            static::$isRegistered = !stream_wrapper_restore('file');
        }

        return static::$isRegistered;
    }

    /**
     * On destruction of the instance, close the underlying file resource, if any.
     *
     * @return void The method does not return any value.
     */
    public function __destruct()
    {
        if (is_resource($this->fileResource)) {
            fclose($this->fileResource);
        }
    }

    /**
     * @throws MonkeyPatchingException
     */
    public function stream_open(string $path, string $mode, int $options, string &$openedPath = null): bool
    {
        self::unregister();

        $absPath = FS::realpath($path);
        $openedPath = !empty($absPath) && is_string($absPath) ? $absPath : $path;
        $useIncludePath = (bool)(STREAM_USE_PATH & $options);

        if (isset(self::$fileToPatcherMap[$openedPath])) {
            $openedPath = $this->patchFile($openedPath);
            unset(self::$fileToPatcherMap[$openedPath]);

            if (empty(self::$fileToPatcherMap)) {
                self::unregister();
            } else {
                self::register();
            }

            return true;
        }

        $this->openFile($openedPath, $mode, $useIncludePath);
        self::register();

        return is_resource($this->fileResource);
    }

    public function stream_flush(): bool
    {
        return is_resource($this->fileResource) && fflush($this->fileResource);
    }

    /**
     * @throws MonkeyPatchingException
     */
    public function stream_read(int $count): string|false
    {
        if ($count < 0) {
            throw new MonkeyPatchingException('Cannot read a negative number of bytes.');
        }

        return is_resource($this->fileResource) ? fread($this->fileResource, $count) : false;
    }

    /**
     * @return resource
     */
    public function stream_cast()
    {
        return $this->fileResource;
    }

    public function stream_lock(int $operation): bool
    {
        // Deal with the fact that PHP might not specify the operation correctly when using `LOCK_EX`.
        $operation = in_array($operation, [LOCK_SH, LOCK_EX, LOCK_UN, LOCK_NB], true) ? $operation : LOCK_EX;
        return is_resource($this->fileResource) && flock($this->fileResource, $operation);
    }

    public function stream_set_option(int $option, int $arg1, int $arg2): bool
    {
        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                return stream_set_blocking($this->fileResource, (bool)$arg1);
            case STREAM_OPTION_READ_TIMEOUT:
                return stream_set_timeout($this->fileResource, $arg1, $arg2);
            case STREAM_OPTION_WRITE_BUFFER:
                $bufferSize = STREAM_BUFFER_NONE === $arg1 ? 0 : $arg2;
                return (bool)stream_set_write_buffer($this->fileResource, $bufferSize);
        }

        return true;
    }

    /**
     * @throws MonkeyPatchingException
     */
    public function stream_write(string $data): int
    {
        $written = fwrite($this->fileResource, $data);

        if ($written === false) {
            throw new MonkeyPatchingException('Could not write to the file.');
        }

        return $written;
    }

    /**
     * @throws MonkeyPatchingException
     */
    public function stream_tell(): int
    {
        $pos = ftell($this->fileResource);

        if ($pos === false) {
            throw new MonkeyPatchingException('Could not get the position of the file pointer.');
        }

        return $pos;
    }

    public function stream_close(): bool
    {
        return fclose($this->fileResource);
    }

    public function stream_eof(): bool
    {
        return feof($this->fileResource);
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        return fseek($this->fileResource, $offset, $whence) === 0;
    }

    public function rename(string $from, string $to): bool
    {
        static::unregister();

        if (isset($this->context)) {
            $result = rename($from, $to, $this->context);
        } else {
            $result = rename($from, $to);
        }

        static::register();

        return $result;
    }

    public function rmdir(string $path, int $options = null): bool
    {
        static::unregister();

        if (isset($this->context)) {
            $result = rmdir($path, $this->context);
        } else {
            $result = rmdir($path);
        }
        static::register();

        return $result;
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
    public function stream_stat(): array|false
    {
        $stat = fstat($this->fileResource);

        if ($stat === false) {
            return false;
        }

        $stat['mtime'] = ($stat['mtime'] ?? 0) + 1;

        return $stat;
    }

    /**
     * @param string|int|array<int,int>|null $value
     *
     * @throws MonkeyPatchingException
     */
    public function stream_metadata(string $path, int $option, string|int|array|null $value): bool
    {
        static::unregister();

        switch ($option) {
            case STREAM_META_TOUCH:
                [$mtime, $atime] = array_replace([null, null], (array)$value);
                $mtime = $mtime !== null ? (int)$mtime : $mtime;
                $atime = $atime !== null ? (int)$atime : $atime;
                /** @noinspection PotentialMalwareInspection */
                $result = touch($path, $mtime, $atime);
                break;
            case STREAM_META_OWNER_NAME:
            case STREAM_META_OWNER:
                if ($value === null || is_array($value)) {
                    throw new MonkeyPatchingException('chown user/group not provided or invalid.');
                }
                $result = chown($path, $value);
                break;
            case STREAM_META_GROUP_NAME:
            case STREAM_META_GROUP:
                if ($value === null || is_array($value)) {
                    throw new MonkeyPatchingException('chgrp user/group not provided or invalid.');
                }
                $result = chgrp($path, $value);
                break;
            case STREAM_META_ACCESS:
                if ($value === null) {
                    throw new MonkeyPatchingException('chmod mode not provided.');
                }
                $result = chmod($path, (int)$value);
                break;
        }

        static::register();

        return $result ?? false;
    }

    public function unlink(string $path): bool
    {
        static::unregister();

        if (isset($this->context)) {
            $result = unlink($path, $this->context);
        } else {
            $result = unlink($path);
        }

        static::register();

        return $result;
    }

    public function stream_truncate(int $newSize): bool
    {
        return ftruncate($this->fileResource, max(0, $newSize));
    }

    public function dir_readdir(): string|false
    {
        return readdir($this->fileResource);
    }

    public function dir_rewinddir(): bool
    {
        rewinddir($this->fileResource);

        return true;
    }

    public function mkdir(string $path, int $mode, bool $recursive): bool
    {
        static::unregister();

        if (isset($this->context)) {
            $result = mkdir($path, $mode, $recursive, $this->context);
        } else {
            $result = mkdir($path, $mode, $recursive);
        }

        static::register();

        return $result;
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
     *
     * @throws ErrorException
     */
    public function url_stat(string $path, int $flags): array|false
    {
        static::unregister();

        if (!(file_exists($path))) {
            if (isset(self::$fileToPatcherMap[$path])) {
                // Ask the patcher to provide stats.
                $stat = self::$fileToPatcherMap[$path]->stat($path);
            } else {
                $stat = false;
            }
            static::register();

            return $stat;
        }

        if (!($flags & STREAM_URL_STAT_LINK) && is_link($path)) {
            // Provide information about the linked file, not the link.
            $path = readlink($path);
        }

        if ($path === false) {
            static::register();
            return false;
        }

        set_error_handler(static function (
            int $errno,
            string $errstr,
            ?string $errfile = null,
            ?int $errline = null,
        ): void {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        try {
            $result = stat($path);
        } catch (Exception $e) {
            if (!($flags & STREAM_URL_STAT_QUIET)) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
            static::register();
            return false;
        }
        restore_error_handler();

        static::register();

        if ($result) {
            // Bump the last modification time.
            $result['mtime']++;
            $result[9]++;
        }

        return $result;
    }

    public function dir_closedir(): bool
    {
        closedir($this->fileResource);

        return true;
    }

    /**
     * @throws MonkeyPatchingException
     */
    public function dir_opendir(string $path, int $options): bool
    {
        static::unregister();

        if (isset($this->context)) {
            $handle = opendir($path, $this->context) ?: null;
        } else {
            $handle = opendir($path) ?: null;
        }

        if (!is_resource($handle)) {
            throw new MonkeyPatchingException("Could not open directory $path");
        }

        $this->fileResource = $handle;

        static::register();

        return $this->fileResource !== null;
    }

    /**
     * @throws MonkeyPatchingException
     */
    private function patchFile(string $absPath): string
    {
        $patcher = self::$fileToPatcherMap[$absPath];
        self::unregister();
        // Do not use `is_file` here as it will use the cached stats: this check should be real.
        $fileContents = file_exists($absPath) ? file_get_contents($absPath) : '';
        self::register();

        if ($fileContents === false) {
            throw new MonkeyPatchingException("Could not read file $absPath contents.");
        }

        [$fileContents, $openedPath] = $patcher->patch($fileContents, $absPath);

        if ($this->context !== null) {
            $fileResource = fopen('php://temp', 'rb+', false, $this->context);
        } else {
            $fileResource = fopen('php://temp', 'rb+');
        }

        if ($fileResource === false) {
            throw new MonkeyPatchingException("Could not open temporary file for writing.");
        }

        $this->fileResource = $fileResource;

        if (fwrite($this->fileResource, $fileContents) === false) {
            throw new MonkeyPatchingException("Could not write to temporary file.");
        }

        rewind($this->fileResource);

        return $openedPath;
    }

    /**
     * @throws MonkeyPatchingException
     */
    private function openFile(string $absPath, string $mode, bool $useIncludePath): void
    {
        if (!file_exists($absPath) && !is_dir(dirname($absPath))) {
            /*
             * The file open operation will never succeed, so we don't even try.
             * The `w`, `c` and `x` modes will create the file if it does not exist,
             * but will not create the directory structure to it
             */
            return;
        }

        if (isset($this->context)) {
            $handle = fopen($absPath, $mode, $useIncludePath, $this->context);
        } else {
            $handle = fopen($absPath, $mode, $useIncludePath);
        }

        if (!is_resource($handle)) {
            return;
            // throw new MonkeyPatchingException("Could not open file $absPath.");
        }

        $this->fileResource = $handle;
    }
}
