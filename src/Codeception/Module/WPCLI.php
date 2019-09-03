<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use tad\WPBrowser\Adapters\Process;
use tad\WPBrowser\Traits\WithWpCli;
use function tad\WPBrowser\buildCommandline;

/**
 * Class WPCLI
 *
 * Wraps calls to the wp-cli tool.
 *
 * @package Codeception\Module
 */
class WPCLI extends Module
{
    use WithWpCli;

    const DEFAULT_TIMEOUT = 60;

    /**
     * An array of keys that will not be passed from the configuration to the wp-cli command.
     *
     * @var array
     */
    protected static $blockedKeys = [
        'throw' => true,
        'timeout' => true,
        'debug' => true,
        'color' => true,
        'prompt' => true,
        'quiet' => true
    ];

    /**
     * @param string $path The absolute path to the target WordPress installation root folder.
     *                     }
     *
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
    protected $bootPath;
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
     * The process timeout.
     *
     * @var int|float|null
     */
    protected $timeout;

    /**
     * WPCLI constructor.
     *
     * @param ModuleContainer $moduleContainer The module container containing this module.
     * @param array|null      $config          The module configuration.
     * @param Process|null    $process         The process adapter.
     */
    public function __construct(ModuleContainer $moduleContainer, $config = null, Process $process = null)
    {
        parent::__construct($moduleContainer, $config);
        $this->wpCliProcess = $process ?: new Process();
    }

    /**
     * Executes a wp-cli command targeting the test WordPress installation.
     *
     * @param string|array $userCommand The string of command and parameters as it would be passed to wp-cli minus `wp`.
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
        $this->validatePath();

        /**
         * Set an environment variable to let client code know the request is coming from the host machine.
         * Set the value to a string to make it so that Symfony\Process will pick it up while populating the env.
         */
        putenv('WPBROWSER_HOST_REQUEST="1"');
        $_ENV['WPBROWSER_HOST_REQUEST'] = '1';

        $userCommand = buildCommandline($userCommand);

        $this->debugSection('command', $userCommand);

        $command = array_merge($userCommand, $this->getConfigOptions($userCommand));

        $this->debugSection('command with configuration options', $command);

        $process = $this->executeWpCliCommand($command, $this->timeout);

        $output = $process->getErrorOutput() ?: $process->getOutput();
        $status = $process->getExitCode();

        $this->debugSection('output', $output);
        $this->debugSection('status', $status);

        $this->evaluateStatus($output, $status);

        return $status;
    }

    /**
     * {@inheritDoc}
     */
    protected function debugSection($title, $message)
    {
        parent::debugSection($this->prettyName . ' ' . $title, $message);
    }

    /**
     * Returns an associative array of wp-cli options parsed from the config array.
     *
     * Users can set additional options that will be passed to the wp-cli command; here is where they are parsed.
     *
     * @param null|string|array $userCommand The user command to parse for inline options.
     *
     * @return array An associative array of options, parsed from the current config.
     */
    protected function getConfigOptions($userCommand = null)
    {
        $inlineOptions = $this->parseWpCliInlineOptions((array)$userCommand);
        $configOptions = array_diff_key($this->config, static::$blockedKeys, $inlineOptions);
        unset($configOptions['path']);

        if (empty($configOptions)) {
            return [];
        }

        return $this->wpCliOptions($configOptions);
    }

    /**
     * Evaluates the exit status of the command.
     *
     * @param string $output The process output.
     * @param int    $status The process status code.
     *
     * @throws ModuleException If the exit status is lt 0 and the module configuration is set to throw.
     */
    protected function evaluateStatus($output, $status)
    {
        if ((int)$status !== 0 && !empty($this->config['throw'])) {
            $output  = is_array($output) ? json_encode($output) : $output;
            $message = "wp-cli terminated with status [{$status}] and output [{$output}]\n\nWPCLI module is configured "
                . 'to throw an exception when wp-cli terminates with an error status; '
                . 'set the `throw` parameter to `false` to avoid this.';

            throw new ModuleException(__CLASS__, $message);
        }
    }

    /**
     * Returns the output of a wp-cli command as an array optionally allowing a callback to process the output.
     *
     * @param string|array $userCommand   The string of command, or commands, and parameters as it would be passed to
     *                                    wp-cli minus `wp`.
     * @param callable     $splitCallback An optional callback function in charge of splitting the results array.
     *
     * @return array An array containing the output of wp-cli split into single elements.
     *
     * @throws \Codeception\Exception\ModuleException If the $splitCallback function does not return an array.
     *
     * @example
     * ```php
     * // Return a list of inactive themes, like ['twentyfourteen', 'twentyfifteen'].
     * $inactiveThemes = $I->cliToArray('theme list --status=inactive --field=name');
     * // Get the list of installed plugins and only keep the ones starting with "foo".
     * $fooPlugins = $I->cliToArray(['plugin', 'list', '--field=name'], function($output){
     *      return array_filter(explode(PHP_EOL, $output), function($name){
     *              return strpos(trim($name), 'foo') === 0;
     *      });
     * });
     * ```
     *
     */
    public function cliToArray($userCommand = 'post list --format=ids', callable $splitCallback = null)
    {
        $this->validatePath();

        /**
         * Set an environment variable to let client code know the request is coming from the host machine.
         * Set the value to a string to make it so that Symfony\Process will pick it up while populating the env.
         */
        putenv('WPBROWSER_HOST_REQUEST="1"');
        $_ENV['WPBROWSER_HOST_REQUEST'] = '1';

        $this->debugSection('command', $userCommand);

        $command = array_merge((array)$userCommand, $this->getConfigOptions($userCommand));

        $this->debugSection('command with configuration options', $command);

        $process = $this->executeWpCliCommand($command, $this->timeout);

        $output = $process->getErrorOutput() ?: $process->getOutput();
        $status = $process->getExitCode();

        $this->debugSection('output', $output);
        $this->debugSection(' status', $status);

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

    /**
     * {@inheritDoc}
     */
    protected function validateConfig()
    {
        parent::validateConfig();
        $this->validateTimeout();
    }

    /**
     * Validates the configuration path to make sure it's a directory.
     *
     * @throws ModuleConfigException If the configuration path is not a directory.
     */
    protected function validatePath()
    {
        if (!is_dir($this->config['path'])) {
            throw new ModuleConfigException(
                __CLASS__,
                'Specified path [' . $this->config['path'] . '] is not a directory.'
            );
        }

        $this->wpCliWpRootDir = realpath($this->config['path']) ?: $this->config['path'];
    }

    /**
     * Validates the configuration timeout.
     *
     * @throws ModuleConfigException If the configuration timeout is not valid.
     */
    protected function validateTimeout()
    {
        $timeout = static::DEFAULT_TIMEOUT;

        if (array_key_exists('timeout', $this->config)) {
            $timeout = empty($this->config['timeout']) ? null : $this->config['timeout'];
        }

        if (!($timeout === null || is_numeric($timeout))) {
            throw new ModuleConfigException($this, "Timeout [{$this->config['timeout']}] is not valid.");
        }

        $this->timeout = $timeout;
    }
}
