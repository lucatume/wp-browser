<?php

namespace tad\WPBrowser\Filesystem\FileReplacers;

use Codeception\Exception\ModuleConfigException;
use tad\WPBrowser\Generators\TemplateProviderInterface;

class AbstractFileReplacer
{

    /**
     * @var string
     */
    protected $original;
    /**
     * @var string
     */
    protected $moved;
    /**
     * @var string The absolute path to the WordPress installation folder.
     */
    protected $path;
    /**
     * @var TemplateProviderInterface
     */
    protected $contentsProvider;

    /**
     * WPConfigFileReplacer constructor.
     */
    public function __construct($path, TemplateProviderInterface $contentsProvider)
    {
        if (!is_string($path)) {
            throw new ModuleConfigException(__CLASS__, 'Root path must be a string');
        }
        if (!is_dir($path)) {
            throw new ModuleConfigException(__CLASS__, 'Root path must point to a directory.');
        }
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!file_exists($path . $this->targetFile)) {
            throw new ModuleConfigException(__CLASS__, 'Root path must contain a "' . $this->targetFile . '" file.');
        }

        $this->path             = $path;
        $this->contentsProvider = $contentsProvider;
        $this->moved            = $this->path . 'original-' . trim($this->targetFile, '.');
        $this->original         = $this->path . $this->targetFile;
    }

    public function replaceOriginal()
    {
        rename($this->original, $this->moved);
        file_put_contents($this->original, $this->contentsProvider->getContents());
    }

    public function restoreOriginal()
    {
        unlink($this->original);
        rename($this->moved, $this->original);
    }
}
