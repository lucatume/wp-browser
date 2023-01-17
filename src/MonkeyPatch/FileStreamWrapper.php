<?php

namespace lucatume\WPBrowser\MonkeyPatch;

use Closure;
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

    public function __construct()
    {
    }

    public static function setPatcherForFile(string $file, PatcherInterface $patcher): void
    {
        self::$fileToPatcherMap[FS::realpath($file)] = $patcher;
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

    private static function noop(): Closure
    {
        return static function () {
        };
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
        $absPathResolved = !empty($absPath) && is_string($absPath);
        $openedPath = $absPathResolved ? $absPath : $path;
        $useIncludePath = (bool)(STREAM_USE_PATH & $options);

        // The stream wrapper will only patch existing files.
        if ($absPathResolved && isset(self::$fileToPatcherMap[$absPath])) {
            $openedPath = $this->patchFile($absPath);
            unset(self::$fileToPatcherMap[$absPath]);

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

    public function stream_read(int $count): string|false
    {
        return is_resource($this->fileResource) ? fread($this->fileResource, $count) : false;
    }

    /**
     * return resource
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
            case  STREAM_OPTION_WRITE_BUFFER:
                $bufferSize = STREAM_BUFFER_NONE === $arg1 ? 0 : $arg2;
                return (bool)stream_set_write_buffer($this->fileResource, $bufferSize);
        }

        return true;
    }

    public function stream_write(string $data): int
    {
        return fwrite($this->fileResource, $data);
    }

    public function stream_tell(): int
    {
        return ftell($this->fileResource);
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
        return fseek($this->fileResource, $offset, $whence);
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

    public function stream_stat(): array
    {
        $stat = fstat($this->fileResource);
        if ($stat) {
            $stat['mtime'] = ($stat['mtime'] ?? 0) + 1;
        }

        return $stat;
    }

    public function stream_metadata(string $path, int $option, $value): bool
    {
        static::unregister();

        switch ($option) {
            case STREAM_META_TOUCH:
                [$mtime, $atime] = array_replace([null, null], (array)$value);
                /** @noinspection PotentialMalwareInspection */
                $result = touch($path, $mtime, $atime);
                break;
            case STREAM_META_OWNER_NAME:
            case STREAM_META_OWNER:
                if ($value === null) {
                    throw new MonkeyPatchingException('chown user/group not provided');
                }
                $result = chown($path, $value);
                break;
            case STREAM_META_GROUP_NAME:
            case STREAM_META_GROUP:
                if ($value === null) {
                    throw new MonkeyPatchingException('chgrp user/group not provided');
                }
                $result = chgrp($path, $value);
                break;
            case STREAM_META_ACCESS:
                if ($value === null) {
                    throw new MonkeyPatchingException('chmod mode not provided');
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
        return ftruncate($this->fileResource, $newSize);
    }

    public function dir_readdir(): string
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

    public function url_stat(string $path, int $flags): array
    {
        static::unregister();

        set_error_handler(self::noop());
        try {
            $result = stat($path);
        } catch (Exception $e) {
            $result = null;
        }
        restore_error_handler();

        static::register();

        if ($result) {
            // Bump the last modification time.
            $result['mtime']++;
            $result[9]++;
        }

        return (array)$result;
    }

    public function dir_closedir(): bool
    {
        closedir($this->fileResource);

        return true;
    }

    public function dir_opendir(string $path, int $options): bool
    {
        static::unregister();

        if (isset($this->context)) {
            $this->fileResource = opendir($path, $this->context) ?: null;
        } else {
            $this->fileResource = opendir($path) ?: null;
        }

        static::register();

        return $this->fileResource !== null;
    }

    private function patchFile(bool|string $absPath): string
    {
        $patcher = self::$fileToPatcherMap[$absPath];
        self::unregister();
        $fileContents = file_get_contents($absPath);
        self::register();

        if ($fileContents === false) {
            throw new MonkeyPatchingException("Could not read file $absPath contents.");
        }

        [$fileContents, $openedPath] = $patcher->patch($fileContents, $absPath);

        $this->fileResource = fopen('php://temp', 'rb+', false, $this->context);

        if ($this->fileResource === false) {
            throw new MonkeyPatchingException("Could not open temporary file for writing.");
        }

        if (fwrite($this->fileResource, $fileContents) === false) {
            throw new MonkeyPatchingException("Could not write to temporary file.");
        }

        rewind($this->fileResource);

        return $openedPath;
    }

    private function openFile(bool|string $absPath, string $mode, bool $useIncludePath): void
    {
        if (isset($this->context)) {
            $handle = fopen($absPath, $mode, $useIncludePath, $this->context);
        } else {
            $handle = fopen($absPath, $mode, $useIncludePath);
        }
        $this->fileResource = is_resource($handle) ? $handle : null;
    }
}
