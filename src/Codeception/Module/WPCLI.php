<?php

namespace Codeception\Module;


use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
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
     * @var array
     */
    protected $config = [
        'throw' => true
    ];

    /**
     * WPCLI constructor.
     *
     * @param ModuleContainer $moduleContainer
     * @param null|array $config
     * @param Executor|null $executor
     *
     * @throws ModuleConfigException If specified path is not a folder.
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
     * @param array  $output If provided the array will be filled with the command output lines.
     *
     * @return int wp-cli exit value for the command
     *
     */
    public function cli($userCommand = 'core version', &$output = [])
    {
        $this->initPaths();

        $command = $this->buildCommand($userCommand);

        $output = [];
        $this->debugSection('command', $command);
        $status = $this->executor->exec($command, $output);
        $this->debugSection('output', $output);

        $this->evaluateStatus($output, $status);

        return $status;
    }

    protected function initPaths()
    {
        if (empty($this->wpCliRoot)) {
            $this->initWpCliPaths();
        }
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

    /**
     * @param $userCommand
     * @return string
     */
    protected function buildCommand($userCommand)
    {
        $mergedCommand = $this->mergeCommandOptions($userCommand);
        $command = implode(' ', [PHP_BINARY, $this->bootPath, $mergedCommand]);
        return $command;
    }

    /**
     * @param string $userCommand
     * @return string
     */
    protected function mergeCommandOptions($userCommand)
    {
        $commonOptions = [
            'path' => $this->config['path'],
        ];

        $lineOptions = [];

        $nonOverriddenOptions = [];
        foreach ($this->options as $key) {
            if ($key !== 'require' && false !== strpos($userCommand, '--' . $key)) {
                continue;
            }
            $nonOverriddenOptions[] = $key;
        }

        foreach ($nonOverriddenOptions as $key) {
            if (isset($this->config[$key])) {
                $commonOptions[$key] = $this->config[$key];
            }
        }

        foreach ($commonOptions as $key => $value) {
            $lineOptions[] = $value === true ? "--{$key}" : "--{$key}={$value}";
        }

        return $userCommand . ' ' . implode(' ', $lineOptions);
    }

    /**
     * @param string $title
     * @param string $message
     */
    protected function debugSection($title, $message)
    {
        parent::debugSection($this->prettyName . ' ' . $title, $message);
    }

    /**
     * @param $output
     * @param $status
     * @throws ModuleException
     */
    protected function evaluateStatus(&$output, $status)
    {
        if (!empty($this->config['throw']) && $status < 0) {
            $output = !is_array($output) ?: json_encode($output);
            $message = "wp-cli terminated with status [{$status}] and output [{$output}]\n\nWPCLI module is configured to throw an exception when wp-cli terminates with an error status; set the `throw` parameter to `false` to avoid this.";
            throw new ModuleException(__CLASS__, $message);
        }
    }

    /**
     * Returns the output of a wp-cli command as an array.
     *
     * This method should be used in conjunction with wp-cli commands that will return lists.
     * E.g.
     *
     *      $inactiveThemes = $I->cliToArray('theme list --status=inactive --field=name');
     *
     * The above command could return an array like
     *
     *      ['twentyfourteen', 'twentyfifteen']
     *
     * No check will be made on the command the user inserted for coherency with a split-able
     * output.
     *
     * @param string        $userCommand
     * @param callable|null $splitCallback A optional callback function in charge of splitting the results array.
     *
     * @return array An array containing the output of wp-cli split into single elements.
     *
     * @throws \Codeception\Exception\ModuleException If the $splitCallback function does not return an array.
     */
    public function cliToArray($userCommand = 'post list --format=ids', callable  $splitCallback = null)
    {
        $this->initPaths();

        $command = $this->buildCommand($userCommand);

        $this->debugSection('command', $command);
        $output = $this->executor->execAndOutput($command, $status);
        $this->debugSection('output', $output);

        $this->evaluateStatus($output, $status);

        if (empty($output)) {
            return [];
        }

        $hasSplitCallback = !is_null($splitCallback);
        $originalOutput = $output;
        if (!is_array($output) || (is_array($output) && $hasSplitCallback)) {
            if (is_array($output)) {
                $output = implode(PHP_EOL, $output);
            }
            if (!$hasSplitCallback) {
                if (!preg_match('/[\\n]+/', $output)) {
                    $output = preg_split('/\\s+/', $output);
                } else {
                    $output = preg_split('/\\s*\\n+\\s*/', $output);
                }
            } else {
                $output = $splitCallback($output, $userCommand, $this);
            }
        }

        if (!is_array($output) && $hasSplitCallback) {
            throw new ModuleException(__CLASS__,
                "Split callback must return an array, it returned: \n" . print_r($output,
                    true) . "\nfor original output:\n" . print_r($originalOutput, true));
        }

        return empty($output) ? [] : array_map('trim', $output);
    }
}