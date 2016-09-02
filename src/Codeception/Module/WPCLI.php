<?php

namespace Codeception\Module;


use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use tad\WPBrowser\Environment\Executor;
use WP_CLI\Configurator;

/**
 * Class WPCLI
 *
 * Wraps calls to the wp-cli tool.
 *
 * @package Codeception\Module
 */
class WPCLI extends Module

{
    /**
     * @var array {
     * @param string $path The absolute path to the target WordPress installation root folder.
     * }
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

    /**
     * WPCLI constructor.
     *
     * @param ModuleContainer $moduleContainer
     * @param null|array $config
     * @param Executor|null $executor
     *
     * @throws ModuleConfigException If specifiec path is not a folder.
     */
    public function __construct(ModuleContainer $moduleContainer, $config, Executor $executor = null)
    {
        parent::__construct($moduleContainer, $config);

        if (!is_dir($config['path'])) {
            throw new ModuleConfigException(__CLASS__, 'Specified path [' . $config['path'] . '] is not a directory.');
        }

        $this->executor = $executor ?: new Executor($this->prettyName);
    }

    /**
     * Executes a wp-cli command.
     *
     * The method is a wrapper around isolated calls to the wp-cli tool.
     * The library will use its own wp-cli version to run the commands.
     *
     * @param string $userCommand The string of command and parameters as it would be passed to wp-cli
     *                            e.g. a terminal call like `wp core version` becomes `core version`
     *                            omitting the call to wp-cli script.
     * @return int wp-cli exit value for the command
     */
    public function cli($userCommand = 'core version')
    {
        if (empty($this->wpCliRoot)) {
            $this->initWpCliPaths();
        }

        $command = implode(' ', [PHP_BINARY, $this->bootPath, $this->getCommonOptions(), $userCommand]);

        $this->debugSection('command', $command);
        $return = $this->executor->exec($command, $output);
        $this->debugSection('output', $output);

        return $return;
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