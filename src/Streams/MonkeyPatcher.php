<?php
/**
 * Monkey-patch file contents.
 *
 * @package lucatume\WPBrowser\Streams;
 */

namespace lucatume\WPBrowser\Streams;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use SplObjectStorage;

/**
 * Class MonkeyPatcher.
 *
 * @package lucatume\WPBrowser\Streams;
 */
class MonkeyPatcher
{
    /**
     * A map from patchers to target files.
     *
     * @since TBD
     *
     * @var SplObjectStorage
     */
    protected static $patchers;

    /**
     * Whether the class is currently registered as wrapper or not.
     */
    private static bool $registered = false;

    /**
     * A map from file paths to the function that should be used to patch them on load.
     *
     * @var array<string,callable>
     */
    private static array $fileTargets = [];

    /**
     * The file extension that should be used for the patched files.
     */
    private static string $patchedFileExtension = 'monkey';
    /**
     * The current resource context, set by the PHP engine.
     *
     * @var resource|null
     */
    public $context;

    /**
     * The underlying file resource, or `null` if not set yet.
     *
     * @var resource|null
     */
    private $fileResource;

    /**
     * Unregisters the Stream wrapper on the file protocol.
     *
     * @reutrn bool Whether the stream wrapper unregistration was successful or not.
     */
    public static function unregister()
    {
        if (self::$registered) {
            self::$registered = ! stream_wrapper_restore('file');
        }

        return self::$registered;
    }

    /**
     * Registers the Stream wrapper on the file protocol.
     *
     * @reutrn bool Whether the stream wrapper registration was successful or not.
     */
    public static function register()
    {
        if (! self::$registered) {
            self::$registered = stream_wrapper_unregister('file') && stream_wrapper_register('file', self::class);
        }

        return self::$registered;
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
     * Opens a file stream and patches it.
     *
     * @param string      $path       The absolute path to the file to open.
     * @param string      $mode       A mode to open the file with, see `fopen` for the supported modes.
     * @param int         $options    A integer aggregating the options to use for the file opening, see the `STREAM`
     *                                constants.
     * @param string|null $openedPath A variable, set by reference, that will specify the absolute path to the
     *                                actually opened file.
     *
     * @return bool Whether the file opening was successful or not.
     *
     * @throws RuntimeException If there's any issue including the file or monkey-patching it.
     */
    public function stream_open($path, $mode, $options, &$openedPath = null)
    {
        self::unregister();

        $realpath = realpath($path);

        if ($realpath === false) {
            throw new RuntimeException("File path $path could not be resolved to realpath.");
        }

        $useIncludePath = (bool)(STREAM_USE_PATH & $options);

        if (! empty(self::$fileTargets[$realpath])) {
            $patcher    = self::$fileTargets[$realpath];
            $openedPath = $realpath;
            $this->setupPatchedFileResource($realpath, $patcher);
            self::register();

            return is_resource($this->fileResource);
        }

        $openedPath = $realpath;
        if (isset($this->context)) {
            $handle             = fopen($realpath, $mode, $useIncludePath, $this->context);
            $this->fileResource = is_resource($handle) ? $handle : null;
        } else {
            $handle             = fopen($realpath, $mode, $useIncludePath);
            $this->fileResource = is_resource($handle) ? $handle : null;
        }

        self::register();

        return is_resource($this->fileResource);
    }

    /**
     * Opens and patches a target file storing a reference to the patched file resource.
     *
     * @param string   $path    The absolute path to the file to patch.
     * @param callable $patcher The callable that will receive the file path and original contents as
     *                          input.
     *
     * @throws RuntimeException If there's any issue opening, patching or storing the patched file.
     */
    private function setupPatchedFileResource($path, callable $patcher)
    {
        $patchedFilePath  = 'php://memory';
        $originalContents = file_get_contents($path);

        if ($originalContents === false) {
            throw new RuntimeException("Could not open file {$path} for read.");
        }

        $patchedCode              = $patcher($path, $originalContents, $patcher);
        self::$fileTargets[$path] = $patcher;
        $stream                   = is_resource($this->context) ?
            fopen($patchedFilePath, 'rb+', false, $this->context)
            : fopen($patchedFilePath, 'rb+', false);

        if (! is_resource($stream)) {
            throw new RuntimeException("Could not open stream for file {$path}.");
        }

        $written = 0;
        do {
            $chunk   = substr($patchedCode, $written, 1024);
            $written = fwrite($stream, $chunk, strlen($chunk));
        } while ($written);

        fseek($stream, 0, SEEK_SET);

        $this->fileResource = $stream;
    }

    /**
     * Flushes the file resource.
     *
     * @return bool Whether the file resource was correctly flushed or not.
     */
    public function stream_flush()
    {
        return is_resource($this->fileResource) && fflush($this->fileResource);
    }

    /**
     * Reads and returns the read amount of bytes.
     *
     * @param int $count The amount of bytes to read.
     *
     * @return false|string Either the read file contents, or `false` if the file resource could not be read.
     */
    public function stream_read($count): false|string
    {
        return is_resource($this->fileResource) ? fread($this->fileResource, $count) : false;
    }

    /**
     * Retrieves the underlying resource.
     *
     * @return resource|false The underlying file resource, or `false` if the underlying file resource cannot be read.
     */
    public function stream_cast()
    {
        return is_resource($this->fileResource) ? $this->fileResource : false;
    }

    /**
     * Attempt a lock on the file resource.
     *
     * @param int $operation A flag indicating the kind of lock operation to perform on the file, see the `LOCK`
     *                       constants.
     *
     * @return bool Whether the lock operation was successful or not.
     */
    public function stream_lock($operation)
    {
        return is_resource($this->fileResource) && flock($this->fileResource, $operation);
    }

    /**
     * Changes an option on the file resource.
     *
     * @param int      $option The option to set the stream, see the `STREAM_OPTION` constants.
     * @param int|bool $arg1   The first value for the option.
     * @param int      $arg2   The second value for the option.
     *
     * @return bool Whether the option setting was successful or not.
     */
    public function stream_set_option($option, int|bool $arg1, $arg2)
    {
        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                return is_resource($this->fileResource) && (bool)stream_set_blocking($this->fileResource, (bool)$arg1);
            case STREAM_OPTION_READ_TIMEOUT:
                return is_resource($this->fileResource) ?
                    (bool)stream_set_timeout($this->fileResource, (int)$arg1, (int)$arg2) : false;
            case STREAM_OPTION_WRITE_BUFFER:
                $bufferSize = STREAM_BUFFER_NONE === (int)$arg1 ? 0 : (int)$arg2;

                return is_resource($this->fileResource) && (bool)stream_set_write_buffer(
                    $this->fileResource,
                    $bufferSize
                );
        }
    }

    /**
     * Writes to the file stream.
     *
     * @param string $data The data to write to the stream.
     *
     * @return false|int The amount of written bytes to the file, or `false` if the write failed.
     */
    public function stream_write($data): false|int
    {
        return is_resource($this->fileResource) ? fwrite($this->fileResource, $data) : false;
    }

    /**
     * Returns the current position in the file stream.
     *
     * @return int The current file stream resource position.
     */
    public function stream_tell()
    {
        return is_resource($this->fileResource) ? ftell($this->fileResource) : false;
    }

    /**
     * Closes the underlying file resource.
     *
     * @return bool Whether the closing was successful or not.
     */
    public function stream_close()
    {
        return is_resource($this->fileResource) && fclose($this->fileResource);
    }

    /**
     * Returns whether the file resource is at the end of the file or not.
     *
     * @return bool Whether the file resource is at the end of the file or not.
     */
    public function stream_eof()
    {
        return is_resource($this->fileResource) && feof($this->fileResource);
    }

    /**
     * Seeks to a specific position in the file resource.
     *
     * @param int $offset The offset to move the file pointer by.
     * @param int $whence The option that should be used to apply the offset, see the `SEEK` constants.
     *
     * @return bool Whether the seeking was successful or not.
     */
    public function stream_seek($offset, $whence = SEEK_SET)
    {
        return is_resource($this->fileResource) ? fseek($this->fileResource, $offset, $whence) : false;
    }

    /**
     * Renames the underlying file.
     *
     * @param string $from The original file name.
     * @param string $to   The new file name.
     *
     * @return bool Whether the renaming was successful or not.
     */
    public function rename($from, $to)
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

    /**
     * Removes a directory.
     *
     * @param string   $path    The path of the directory to remove.
     * @param int|null $options A bitwise mask of options.
     *
     * @return bool Whether the director removal was successful or not.
     */
    public function rmdir($path, $options = null)
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
     * Returns the information about the file resource.
     *
     * @return array<string|int,float|int|false> A map of the file stats.
     */
    public function stream_stat()
    {
        $stat = is_resource($this->fileResource) ? fstat($this->fileResource) : false;
        if ($stat) {
            $stat['mtime'] = ($stat['mtime'] ?? 0) + 1;
        }

        return $stat;
    }

    /**
     * Changes the stream metadata.
     *
     * @param string                $path   The path of the file to set the metadata of.
     * @param int                   $option An option to use, one of the `STREAM_META` constants.
     * @param array<int>|int|string $value  The value for the stream metadata to apply.
     *
     * @return bool Whether the application of the stream metadata was successful or not.
     */
    public function stream_metadata($path, $option, array|int|string $value)
    {
        static::unregister();

        switch ($option) {
            case STREAM_META_TOUCH:
                [$mtime, $atime] = array_replace([null, null], (array)$value);
                $result = touch($path, $mtime, $atime);
                break;
            case STREAM_META_OWNER_NAME:
            case STREAM_META_OWNER:
                if ($value === null) {
                    throw new RuntimeException('chown user/group not provided');
                }
                $result = chown($path, $value);
                break;
            case STREAM_META_GROUP_NAME:
            case STREAM_META_GROUP:
                if ($value === null) {
                    throw new RuntimeException('chgrp user/group not provided');
                }
                $result = chgrp($path, $value);
                break;
            case STREAM_META_ACCESS:
                if ($value === null) {
                    throw new RuntimeException('chmod mode not provided');
                }
                $result = chmod($path, (int)$value);
                break;
        }

        static::register();

        return $result ?? false;
    }

    /**
     * Deletes a file.
     *
     * @param string $path The path to the file to delete.
     *
     * @return bool Whether the file deletion was successful or not.
     */
    public function unlink($path)
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

    /**
     * Truncates the stream.
     *
     * @param int $newSize The new size, in bytes, to truncate the stream to.
     *
     * @return bool Whether the file truncation was successful or not.
     */
    public function stream_truncate($newSize)
    {
        return is_resource($this->fileResource) && ftruncate($this->fileResource, $newSize);
    }

    /**
     * Read entry from a directory handle.
     *
     * @return string|false The next file name, or `false` is there is no more files in the directory.
     */
    public function dir_readdir(): string|false
    {
        return is_resource($this->fileResource) ? readdir($this->fileResource) : false;
    }

    /**
     * Rewind directory handle.
     *
     * @return bool Whether the rewind was successful or not.
     */
    public function dir_rewinddir()
    {
        if (is_resource($this->fileResource)) {
            rewinddir($this->fileResource);
        }

        return true;
    }

    /**
     * Create a directory.
     *
     * @param string $path      The path to the directory that should be created.
     * @param int    $mode      The file mode that should be applied to the created directory.
     * @param bool   $recursive Whether the directory should be created recursively or not.
     *
     * @return bool Whether the directory creation was successful or not.
     */
    public function mkdir($path, $mode, $recursive)
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
     * Retrieves information about a file.
     *
     * @param string $path  The path to fetch information for.
     * @param int    $flags Holds additional flags set by the streams API, see the `STREAM_URL` constants.
     *
     * @return array<int|string,int|false>|false A map of the file stats.
     */
    public function url_stat($path, $flags): array|false
    {
        static::unregister();

        set_error_handler(static function () {
        });
        try {
            $result = stat($path);
        } catch (Exception) {
            $result = null;
        }
        restore_error_handler();

        static::register();

        if ($result && is_array($result)) {
            // Bump the last modification time.
            $result['mtime']++;
            $result[9]++;
        }

        return $result;
    }

    /**
     * Closes a directory handle.
     *
     * @return bool Whether the directory closing was successful or not.
     */
    public function dir_closedir()
    {
        if (is_resource($this->fileResource)) {
            closedir($this->fileResource);
        }

        return true;
    }

    /**
     * Opens a directory handle.
     *
     * @param string $path    The path to the directory to open.
     * @param int    $options Holds additional flags set by the streams API.
     *
     * @return bool Whether the directory opening was successful or not.
     */
    public function dir_opendir($path, $options)
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

    /**
     * Patches the content of a file at most once.
     *
     * @since TBD
     *
     * @param string   $file    The path to the file to patch.
     * @param callable $patcher A callable object or Closure that will be provided the file path
     *                          and contents as parameters.
     *
     * @return void
     */
    public static function patchFileWith($file, $patcher)
    {
        if (! self::$patchers instanceof SplObjectStorage) {
            self::$patchers = new SplObjectStorage();
        }

        self::unregister();
        $realpath = realpath($file);

        if ($realpath === false) {
            throw new InvalidArgumentException("File $file does not exist.");
        }

        self::$fileTargets[realpath($file)] = $patcher;
        if (self::$patchers->contains($patcher)) {
            self::$patchers[$patcher][] = $file;
        } else {
            self::$patchers[$patcher] = [$file];
        }
        self::register();
    }

    /**
     * Removes a patcher from each file target it's applied to.
     *
     * @since TBD
     *
     * @param callable $patcher The patcher to remove.
     *
     * @return void
     */
    public static function removePatcher(callable $patcher)
    {
        if (! self::$patchers->contains($patcher)) {
            return;
        }
        foreach (self::$patchers[$patcher] as $file) {
            unset(self::$fileTargets[$file]);
        }
        unset(self::$patchers[$patcher]);
    }
}
