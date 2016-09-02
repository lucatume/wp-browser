<?php

namespace Codeception\Module;


use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use tad\WPBrowser\Environment\Executor;
use WP_CLI\Configurator;

class WPCLI extends Module

{
    /**
     * @var array
     */
    protected $requiredFields = ['path'];

    /**
     * @var string
     */
    protected $prettyName = 'WPCLI';

    /**
     * @var string
     */
    protected $wpCliRoot = '';

    /**
     * @var string
     */
    protected $bootPath;

    /**
     * @var Executor
     */
    protected $executor;

    /**
     * @var array
     */
    protected $options = ['ssh', 'http', 'url', 'user', 'skip-plugins', 'skip-themes', 'skip-packages', 'require'];

    public function __construct(ModuleContainer $moduleContainer, $config, Executor $executor = null)
    {
        parent::__construct($moduleContainer, $config);

        if (!is_dir($config['path'])) {
            throw new ModuleConfigException(__CLASS__, 'Specified path [' . $config['path'] . '] is not a directory.');
        }

        $this->executor = $executor ?: new Executor($this->prettyName);
    }

    public function cli($userCommand = 'core version')
    {
        if (empty($this->wpCliRoot)) {
            $this->initWpCliPaths();
        }

        $command = implode(' ', [PHP_BINARY, $this->bootPath, $this->getCommonOptions(), $userCommand]);

        return $this->executor->exec($command, $output);
    }

    /**
     * Initializes the wp-cli root location.
     *
     * The way the location works is an ugly hack that assumes the folder structure
     * of the code to climb the tree and find the root folder.
     */
    protected function initWpCliPaths()
    {
        $ref = new \ReflectionClass(Configurator::class);
        $this->wpCliRoot = dirname(dirname(dirname($ref->getFileName())));
        $this->bootPath = $this->wpCliRoot . '/php/boot-fs.php';
    }

    private function getCommonOptions()
    {
        $commonOptions = [
            'path' => $this->config['path'],
            'debug' => true
        ];

        foreach ($this->options as $key) {
            if (isset($this->config[$key])) {
                $commonOptions[$key] = $this->config[$key];
            }
        }

        $lineOptions = [];

        foreach ($commonOptions as $key => $value) {
            $lineOptions[] = $value === true ? "--{$key}" : "--{$key}={$value}";
        }

        return implode(' ', $lineOptions);
    }

    protected function debugSection($title, $message)
    {
        parent::debugSection($this->prettyName . ' ' . $title, $message);
    }
}