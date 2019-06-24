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
    const DEFAULT_TIMEOUT = 60;
    /**
     * @param string $path The absolute path to the target WordPress installation root folder.
     * }
     * @var array {
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
     * An array of configuration variables and their default values.
     *
     * @var array
     */
    protected $config = [
        'throw' => true,
        'timeout' => 60,
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
            throw new ModuleConfigException(
                __CLASS__,
                'Specified path [' . $config['path'] . '] is not a directory.'
            );
        }

        $this->executor = $executor ?: new Executor();

        try {
            $this->executor->setTimeout($this->getConfigTimeout($config));
        } catch (\InvalidArgumentException $e) {
            throw new ModuleConfigException($this, $e->getMessage());
        }
    }

    /**
     * Parses and returns the timeout from the configuration array.
     *
     * @param array $config The configuration array to parse.
     *
     * @return int|null The timeout value or `null` to indicate a timeout is not set.
     */
    protected function getConfigTimeout($config)
    {
        $timeout = static::DEFAULT_TIMEOUT;

        if (array_key_exists('timeout', $config)) {
            $timeout = empty($config['timeout']) ? null : $config['timeout'];
        }

        return $timeout;
    }

    /**
     * Executes a wp-cli command targeting the test WordPress installation.
     *
     * @param string $userCommand The string of command and parameters as it would be passed to wp-cli minus `wp`.
     *
     * @return int The command exit value; `0` usually means success.
     *
     * @throws \Codeception\Exception\ModuleException If the status evaluates to non-zero and the `throw` configuration
     *                                                parameter is set to `true`.
     * @example
     * ```php
     * // Activate a plugin via wp-cli in the test WordPress site.
     * $I->cli('plugin activate my-plugin');
     * // Change a user password.
     * $I->cli('user update luca --user_pass=newpassword');
     * ```
     *
     */
    public function cli($userCommand = 'core version')
    {
        /**
         * Set an environment variable to let client code know the request is coming from the host machine.
         * Set the value to a string to make it so that Symfony\Process will pick it up while populating the env.
         */
        putenv('WPBROWSER_HOST_REQUEST="1"');
        $_ENV['WPBROWSER_HOST_REQUEST'] = '1';

        $this->initPaths();

        $command = $this->buildCommand($userCommand);

        $output = [];
        $this->debugSection('command', $command);
        $status = $this->executor->exec($command, $output);
        $this->debugSection('output', implode("\n", (array)$output));

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
     *
     * @throws \Codeception\Exception\ModuleException If the embedded WPCLI Configurator class file
     *                                                could not be found.
     */
    protected function initWpCliPaths()
    {
        try {
            $ref = new \ReflectionClass(Configurator::class);
        } catch (\ReflectionException $e) {
            throw new ModuleException(__CLASS__, 'could not find the path to embedded WPCLI Configurator class');
        }

        $this->wpCliRoot = dirname($ref->getFileName()) . '/../../';

        $wpCliRootRealPath = realpath($this->wpCliRoot);

        if (!empty($wpCliRootRealPath)) {
            $this->wpCliRoot = $wpCliRootRealPath;
        }

        if (!is_dir($this->wpCliRoot)) {
            throw new ModuleException(
                $this,
                "wp-cli root folder ({$this->wpCliRoot}) does not exist."
            );
        }

        $this->debugSection('WPCLI Module', 'wp-cli root path: ' . $this->wpCliRoot);

        $this->bootPath = rtrim($this->wpCliRoot, '\\/') . '/php/boot-fs.php';

        if (!file_exists($this->bootPath)) {
            throw new ModuleException(
                $this,
                'Expected the "boot-fs.php" to  be in "' . $this->bootPath . '" but the file does not exist.'
            );
        }

        $this->debugSection('WPCLI Module', 'boot-fs.php path: ' . $this->bootPath);
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
     * @param $userCommand
     * @return string
     */
    protected function buildCommand($userCommand)
    {
        $mergedCommand = $this->mergeCommandOptions($userCommand);

        return implode(' ', [escapeshellarg(PHP_BINARY), escapeshellarg($this->bootPath), $mergedCommand]);
    }

    /**
     * @param string $userCommand
     * @return string
     */
    protected function mergeCommandOptions($userCommand)
    {
        $commonOptions = [
            'path' => escapeshellarg($this->config['path']),
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
                $commonOptions[$key] = escapeshellarg($this->config[$key]);
            }
        }

        foreach ($commonOptions as $key => $value) {
            $lineOptions[] = $value === true ? "--{$key}" : "--{$key}={$value}";
        }

        return $userCommand . ' ' . implode(' ', $lineOptions);
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
            $message = "wp-cli terminated with status [{$status}] and output [{$output}]\n\nWPCLI module is configured "
                . 'to throw an exception when wp-cli terminates with an error status; '
                . 'set the `throw` parameter to `false` to avoid this.';

            throw new ModuleException(__CLASS__, $message);
        }
    }

    /**
     * Returns the output of a wp-cli command as an array optionally allowing a callback to process the output.
     *
     * @param string $userCommand The string of command and parameters as it would be passed to wp-cli minus `wp`.
     * @param callable $splitCallback An optional callback function in charge of splitting the results array.
     *
     * @return array An array containing the output of wp-cli split into single elements.
     *
     * @throws \Codeception\Exception\ModuleException If the $splitCallback function does not return an array.
     * @example
     * ```php
     * // Return a list of inactive themes, like ['twentyfourteen', 'twentyfifteen'].
     * $inactiveThemes = $I->cliToArray('theme list --status=inactive --field=name');
     * // Get the list of installed plugins and only keep the ones starting with "foo".
     * $fooPlugins = $I->cliToArray('plugin list --field=name', function($output){
     *      return array_filter(explode(PHP_EOL, $output), function($name){
     *              return strpos(trim($name), 'foo') === 0;
     *      });
     * });
     * ```
     *
     */
    public function cliToArray($userCommand = 'post list --format=ids', callable $splitCallback = null)
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

        $hasSplitCallback = null !== $splitCallback;
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
            throw new ModuleException(
                __CLASS__,
                "Split callback must return an array, it returned: \n" . print_r(
                    $output,
                    true
                ) . "\nfor original output:\n" . print_r(
                    $originalOutput,
                    true
                )
            );
        }

        return empty($output) ? [] : array_map('trim', $output);
    }
}
