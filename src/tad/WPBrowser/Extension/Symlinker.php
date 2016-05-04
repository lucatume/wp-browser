<?php

namespace tad\WPBrowser\Extension;


use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use Symfony\Component\Filesystem\Exception\IOException;
use tad\WPBrowser\Filesystem\Filesystem;

class Symlinker extends Extension
{
    public static $events = [
        'module.init' => 'symlink',
        'result.print.after' => 'unlink'
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

    public function __construct($config, $options, Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ? $filesystem : new Filesystem();
        parent::__construct($config, $options);
    }

    public function symlink(\Codeception\Event\SuiteEvent $e)
    {
        $rootFolder = rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR);
        $destination = rtrim($this->config['destination'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($rootFolder);

        try {
            if (!$this->filesystem->fileExists($destination)) {
                $this->filesystem->symlink($rootFolder, $destination, true);
                $this->writeln('Symbolically linked plugin folder [' . $destination . ']');
            }
        } catch (IOException $e) {
            throw  new ExtensionException(__CLASS__, "Error while trying to symlink plugin or theme to destination.\n\n" . $e->getMessage());
        } 
    }

    public function unlink(\Codeception\Event\PrintResultEvent $e)
    {
        $rootFolder = rtrim(codecept_root_dir(), DIRECTORY_SEPARATOR);
        $destination = rtrim($this->config['destination'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($rootFolder);
        if ($this->filesystem->fileExists($destination)) {
            $unlinked = $this->filesystem->unlink($destination);
            if (!$unlinked) {
                // let's not kill the suite but let's notify the user
                $this->writeln('Could not unlink file [' . $destination . '], manual removal is required.');
            }

            $this->writeln('Unliked plugin folder [' . $destination . ']');
        }
    }

    public function _initialize()
    {
        parent::_initialize();
        $this->checkRequirements();
    }

    protected function checkRequirements()
    {
        if (!isset($this->config['mode'])) {
            throw new ExtensionException(__CLASS__, 'Required configuration parameter [mode] is missing.');
        }
        if (!array_intersect($this->required['mode'], (array)$this->config['mode'])) {
            throw new ExtensionException(__CLASS__, '[mode] should be one among these values: [' . implode(', ', $this->required['mode']) . ']');
        }
        if (!isset($this->config['destination'])) {
            throw new ExtensionException(__CLASS__, 'Required configuration parameter [destination] is missing.');
        }
        if (!($this->filesystem->isDir($this->config['destination']) && $this->filesystem->isWriteable($this->config['destination']))) {
            throw new ExtensionException(__CLASS__, '[destination] parameter [' . $this->config['destination'] . '] is not an existing and writeable directory.');
        }
    }
}

