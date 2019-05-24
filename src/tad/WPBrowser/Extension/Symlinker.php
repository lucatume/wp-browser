<?php

namespace tad\WPBrowser\Extension;

use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use tad\WPBrowser\Filesystem\Filesystem;

class Symlinker extends Extension
{
    public static $events = [
        Events::MODULE_INIT => 'symlink',
        Events::SUITE_AFTER => 'unlink',
    ];

    /**
     * @var array
     */
    protected $required = ['mode' => ['plugin', 'theme'], 'destination'];

    /**
     * @var string
     */
    protected $destination;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Symlinker constructor.
     *
     * @param array $config The configuration contents.
     * @param array $options An array of options..
     * @param Filesystem|null $filesystem A wrapper around file system operations.
     */
    public function __construct($config, $options, Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ? $filesystem : new Filesystem();
        parent::__construct($config, $options);
    }

    /**
     * Symbolically links one or more source folders to one or more destinations.
     *
     * @param \Codeception\Event\SuiteEvent $e The event the method is running on.
     * @throws ExtensionException If there are issues with the source or destination folders.
     */
    public function symlink(\Codeception\Event\SuiteEvent $e)
    {
        $rootFolder = $this->getRootFolder($e->getSettings());
        $destination = $this->getDestination($rootFolder, $e->getSettings());

        try {
            if (!$this->filesystem->file_exists($destination)) {
                $this->filesystem->symlink($rootFolder, $destination, true);
                $this->writeln('Symbolically linked plugin folder [' . $destination . ']');
            }
        } catch (IOException $e) {
            throw new ExtensionException(
                __CLASS__,
                "Error while trying to symlink plugin or theme to destination.\n\n" . $e->getMessage()
            );
        }
    }

    /**
     * Returns the root folder for a symlink.
     *
     * @return string The root folder path.
     */
    protected function getRootFolder(array $settings = null)
    {
        $rootFolder = isset($this->config['rootFolder']) ?
            $this->config['rootFolder']
            : rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR);

        if (is_array($rootFolder)) {
            $currentEnvs = $this->getCurrentEnvsFromSettings($settings);
            $fallbackRootFolder = isset($rootFolder['default']) ?
                $rootFolder['default']
                : reset($rootFolder);
            $supportedEnvs      = array_intersect(array_keys($rootFolder), $currentEnvs);
            $firstSupported     = reset($supportedEnvs);
            $rootFolder         = isset($rootFolder[$firstSupported]) ?
                $rootFolder[$firstSupported] :
                $fallbackRootFolder;
        }

        return $rootFolder;
    }

    /**
     * Returns the symlink destination folder.
     *
     * @param string $rootFolder The root folder path.
     * @param array $settings The current extension settings.
     * @return array|string The destination path or an array of destination paths.
     */
    protected function getDestination($rootFolder, array $settings = null)
    {
        $destination = $this->config['destination'];

        if (is_array($destination)) {
            $currentEnvs = $this->getCurrentEnvsFromSettings($settings);
            $fallbackDestination = isset($destination['default']) ? $destination['default'] : reset($destination);
            $supportedEnvs = array_intersect(array_keys($destination), $currentEnvs);
            $firstSupported = reset($supportedEnvs);
            $destination = isset($destination[$firstSupported]) ? $destination[$firstSupported] : $fallbackDestination;
        }
        $destination = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($rootFolder);

        return $destination;
    }

    /**
     * Unlinks the plugin(s) symbolically linked by the extension.
     *
     * @param \Codeception\Event\SuiteEvent $e The suite event the operation is hooking on.
     */
    public function unlink(\Codeception\Event\SuiteEvent $e)
    {
        $rootFolder = $this->getRootFolder($e->getSettings());
        $destination = $this->getDestination($rootFolder, $e->getSettings());

        if ($this->filesystem->file_exists($destination)) {
            try {
                $unlinked = $this->filesystem->unlink($destination);
                if (!$unlinked) {
                    // Let's not kill the suite but let's notify the user.
                    $this->writeln(
                        sprintf(
                            'Could not unlink file [%s], manual removal is required.',
                            $destination
                        )
                    );
                }
            } catch (\Exception $e) {
                // Let's not kill the suite but let's notify the user.
                $this->writeln(sprintf(
                    "There was an error while trying to unlink file [%s], manual removal is required.\nError: %s",
                    $destination,
                    $e->getMessage()
                ));
                return;
            }

            $this->writeln('Unliked plugin folder [' . $destination . ']');
        }
    }

    /**
     * Initializes the extension, checking its settings.
     *
     * @throws ExtensionException If the settings are not valid.
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->checkRequirements();
    }

    /**
     * Checks that the extension broader requirements are met.
     *
     * @throws ExtensionException If one or more requirements are not satisfied.
     */
    protected function checkRequirements()
    {
        if (!isset($this->config['mode'])) {
            throw new ExtensionException(__CLASS__, 'Required configuration parameter [mode] is missing.');
        }
        if (!array_intersect($this->required['mode'], (array)$this->config['mode'])) {
            throw new ExtensionException(
                __CLASS__,
                '[mode] should be one among these values: [' . implode(', ', $this->required['mode']) . ']'
            );
        }
        if (!isset($this->config['destination'])) {
            throw new ExtensionException(__CLASS__, 'Required configuration parameter [destination] is missing.');
        }

        $destination = $this->config['destination'];

        if (is_array($destination)) {
            array_walk($destination, [$this, 'checkDestination']);
        } else {
            $this->checkDestination($destination);
        }

        if (isset($this->config['rootFolder'])) {
            $rootFolder = $this->config['rootFolder'];
            if (is_array($rootFolder)) {
                array_walk($rootFolder, [$this, 'checkRootFolder']);
            } else {
                $this->checkRootFolder($rootFolder);
            }
        }
    }

    /**
     * Checks the destination folder to make sure it exists and is writable.
     *
     * @param string $destination The path to the destination folder.
     * @throws ExtensionException If the destination folder does not exist or is not writable.
     */
    protected function checkDestination($destination)
    {
        if (!($this->filesystem->is_dir($destination) && $this->filesystem->is_writeable($destination))) {
            throw new ExtensionException(
                __CLASS__,
                '[destination] parameter [' . $destination . '] is not an existing and writeable directory.'
            );
        }
    }

    /**
     * Checks the root folder specified in the settings to make sure it exists and it's writeable.
     *
     * @param string $rootFolder The path to the root folder.
     * @throws ExtensionException If the root folder does not exist or is not readable.
     */
    protected function checkRootFolder($rootFolder)
    {
        if (!($this->filesystem->is_dir($rootFolder) && $this->filesystem->is_readable($rootFolder))) {
            throw new ExtensionException(
                __CLASS__,
                '[rootFolder] parameter [' . $rootFolder . '] is not an existing and readable directory.'
            );
        }
    }

    /**
     * Parses and returns the environments, if any, from the settings.
     *
     * @param array $settings The settings array.
     *
     * @return array The environment(s) found in the settings, or `default` if none was found.
     */
    protected function getCurrentEnvsFromSettings(array $settings)
    {
        $rawCurrentEnvs = empty($settings['current_environment']) ? 'default' : $settings['current_environment'];

        return preg_split('/\\s*,\\s*/', $rawCurrentEnvs);
    }

    /**
     * Sets the output the instance should use.
     *
     * @param OutputInterface $output The output the instance should use.
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }
}
