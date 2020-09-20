<?php
/**
 * An extension that will copy directories and files from the source the destination before tests.
 *
 * @package tad\WPBrowser\Extension
 */

namespace tad\WPBrowser\Extension;

use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use tad\WPBrowser\Filesystem\Utils;
use function tad\WPBrowser\recurseCopy;
use function tad\WPBrowser\recurseRemoveDir;

/**
 * Class Copier
 *
 * @package tad\WPBrowser\Extension
 */
class Copier extends Extension
{
    /**
     * A map of the events and callbacks the extension hooks on.
     * @var array<string,string>
     */
    public static $events = [
        Events::MODULE_INIT => 'copyFiles',
        Events::SUITE_AFTER => 'removeFiles'
    ];

    /**
     * Copier constructor.
     *
     * @param array<string,mixed> $config The extension configuration.
     * @param array<string,mixed> $options The extension options.
     */
    public function __construct($config, $options)
    {
        if (!empty($config['files'])) {
            $sources = array_keys($config['files']);
            array_walk($sources, [$this, 'ensureSource']);
            array_walk($config['files'], [$this, 'ensureDestination']);
        }

        parent::__construct($config, $options);
    }

    /**
     * Copies the directory and files from the extension configuration.
     *
     * @return void
     */
    public function copyFiles()
    {
        if (empty($this->config['files'])) {
            return;
        }

        array_walk($this->config['files'], [$this, 'copy']);
    }

    /**
     * Removes the copied directories and files.
     *
     * @return void
     */
    public function removeFiles()
    {
        if (empty($this->config['files'])) {
            return;
        }

        array_walk($this->config['files'], [$this, 'remove']);
    }

    /**
     * Checks the source to ensure it's accessible and readable.
     *
     * @param string $source The path to the source directory or file.
     *
     * @return void
     *
     * @throws ExtensionException If the source directory or file is not readable or not accessible.
     */
    protected function ensureSource($source)
    {
        if (!(
            file_exists($source)
            || file_exists(getcwd() . DIRECTORY_SEPARATOR . trim($source, DIRECTORY_SEPARATOR))
        )) {
            throw new ExtensionException($this, sprintf('Source file [%s] does not exist.', $source));
        }

        if (!is_readable($source)) {
            throw new ExtensionException($this, sprintf('Source file [%s] is not readable.', $source));
        }
    }

    /**
     * Checks a destination directory or file are accessible.
     *
     * @param string $destination The path to the copy destination.
     *
     * @return void
     *
     * @throws ExtensionException If the destination is not accessible.
     */
    protected function ensureDestination($destination)
    {
        $filename = dirname($destination);

        if (!(is_dir($filename))) {
            throw new ExtensionException($this, sprintf('Destination parent dir [%s] does not exist.', $filename));
        }

        if (!is_writable($filename)) {
            throw new ExtensionException($this, sprintf('Destination parent dir [%s] is not writeable.', $filename));
        }

        if (file_exists($destination)) {
            $this->remove($destination);
        }
    }

    /**
     * Removes a previously created destination directory or file.
     *
     * @param string $destination The absolute path to the destination to remove.
     *
     * @return void
     *
     * @throws ExtensionException If the destination directory of file removal fails.
     */
    protected function remove($destination)
    {
        if (!file_exists($destination)) {
            return;
        }

        if (!is_dir($destination)) {
            if (!unlink($destination)) {
                throw new ExtensionException(
                    $this,
                    sprintf('Removal of [%s] failed.', $destination)
                );
            }
            return;
        }

        if (!recurseRemoveDir($destination)) {
            throw new ExtensionException(
                $this,
                sprintf('Removal of [%s] failed.', $destination)
            );
        }
    }

    /**
     * Copies one source to one destination.
     *
     * @param string $destination The absolute path to the destination.
     * @param string $source The absolute path to the source.
     *
     * @return void
     *
     * @throws ExtensionException If the copy from the source to the destination fails.
     */
    protected function copy($destination, $source)
    {
        if (!is_dir($source)) {
            copy($source, $destination);
            return;
        }
        if (!recurseCopy($source, $destination)) {
            throw new ExtensionException(
                $this,
                sprintf('Copy of [%s:%s] failed.', $source, $destination)
            );
        }
    }
}
