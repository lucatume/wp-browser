<?php
/**
 * Wraps calls to the wp-cli tool.
 *
 * @package Codeception\Module
 */

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Module;
use lucatume\WPBrowser\Utils\Filesystem;
use lucatume\WPBrowser\WordPress\CliProcess;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

/**
 * Class WPCLI
 *
 * @package Codeception\Module
 */
class WPCLI extends Module
{
    public const DEFAULT_TIMEOUT = 60;

    /**
     * A list of the module required fields.
     *
     * @var array<string>
     */
    protected array $requiredFields = ['path'];

    /**
     * An array of configuration variables and their default values.
     *
     * @var array<string,mixed>
     */
    protected array $config = [
        'throw' => true,
        'timeout' => 60,
    ];

    private ?CliProcess $lastCliProcess = null;

    /**
     * Executes a wp-cli command targeting the test WordPress installation.
     *
     * @example
     * ```php
     * // Activate a plugin via wp-cli in the test WordPress site.
     * $I->cli(['plugin', 'activate', 'my-plugin']);
     * // Change a user password.
     * $I->cli(['user', 'update', 'luca', '--user_pass=newpassword']);
     * ```
     * @param array<string> $command The command to execute.
     * @param array<string,string|false>|null $env An array of environment variables to pass to the command.
     * @param mixed|null $input The input to pass to the command, a stream resource or a \Traversable
     *                                             instance.
     *
     * @return int The command exit value; `0` usually means success.
     *
     * @throws ModuleException If the command execution fails and the `throw` configuration option is set to `true`.
     */
    public function cli(array $command = ['core', 'version'], ?array $env = null, mixed $input = null): int
    {
        $env = array_replace($this->getDefaultEnv(), (array)($this->config['env'] ?? []), (array)$env);
        $command = $this->addStrictOptionsFromConfig($command);

        $cliProcess = new CliProcess($command, $this->config['path'], $env, $input, $this->config['timeout']);

        $this->debugSection('command', $cliProcess->getCommandLine());

        try {
            $cliProcess->run();
        } catch (ProcessTimedOutException) {
            throw new ModuleException(
                __CLASS__,
                sprintf(
                    'The command "%s" timed out after %d seconds.',
                    $cliProcess->getCommandLine(),
                    $this->config['timeout']
                )
            );
        }

        $this->lastCliProcess = $cliProcess;

        $this->debugSection('STDOUT', $cliProcess->getOutput());
        $this->debugSection('STDERR', $cliProcess->getErrorOutput());

        /** @var int $exitCode The process terminated at this stage. */
        $exitCode = $cliProcess->getExitCode();

        $this->debugSection('exit code', print_r($exitCode, true));

        if ($this->config['throw'] && $exitCode !== 0) {
            throw new ModuleException(
                __CLASS__,
                sprintf(
                    'The command "%s" failed with exit code %d.',
                    $cliProcess->getCommandLine(),
                    $exitCode
                )
            );
        }

        return $exitCode;
    }

    protected function debugSection(string $title, mixed $message): void
    {
        parent::debugSection("WPCLI $title", $message);
    }

    /**
     * @throws ModuleException
     */
    public function grabLastCliProcess(): CliProcess
    {
        if (!$this->lastCliProcess instanceof CliProcess) {
            throw new ModuleException(
                __CLASS__,
                'No command has run yet; cannot grab last cli process.'
            );
        }
        return $this->lastCliProcess;
    }

    /**
     * Returns the shell output of the last command.
     *
     * @return string The output produced by the last shell command.
     *
     * @throws ModuleException If no prior command ran.
     */
    public function grabLastShellOutput(): string
    {
        if (!$this->lastCliProcess instanceof CliProcess) {
            throw new ModuleException(
                __CLASS__,
                'No command has run yet; cannot grab output.'
            );
        }

        return $this->lastCliProcess->getOutput();
    }

    /**
     * Returns the shell error output of the last command.
     *
     * @return string The error output produced by the last shell command, if any.
     *
     * @throws ModuleException If no prior command ran.
     */
    public function grabLastShellErrorOutput(): string
    {
        if (!$this->lastCliProcess instanceof CliProcess) {
            throw new ModuleException(
                __CLASS__,
                'No command has run yet; cannot grab error output.'
            );
        }

        return $this->lastCliProcess->getErrorOutput();
    }


    /**
     * Returns the output of a wp-cli command as an array optionally allowing a callback to process the output.
     *
     * @example
     * ```php
     * // Return a list of inactive themes, like ['twentyfourteen', 'twentyfifteen'].
     * $inactiveThemes = $I->cliToArray(['theme', 'list', '--status=inactive', '--field=name']);
     * // Get the list of installed plugins and only keep the ones starting with "foo".
     * $fooPlugins = $I->cliToArray(['plugin', 'list', '--field=name'], function($output){
     *      return array_filter(explode(PHP_EOL, $output), function($name){
     *              return strpos(trim($name), 'foo') === 0;
     *      });
     * });
     * ```
     * @param array<string> $command The string of command and parameters as it would be passed to wp-cli
     *                                             minus `wp`. For back-compatibility purposes you can still pass the
     *                                             commandline as a string, but the array format is the preferred and
     *                                             supported method.
     * @param callable|null $splitCallback An optional callback function to split the results array.
     * @param array<string,string|false>|null $env An array of environment variables to pass to the command.
     * @param mixed $input The input to pass to the command, a stream resource or a \Traversable
     *                                             instance.
     *
     * @return array<string> An array containing the output of wp-cli split into single elements.
     *
     * @throws ModuleException
     */
    public function cliToArray(
        array $command,
        callable $splitCallback = null,
        ?array $env = null,
        mixed $input = null
    ): array {
        $this->cli($command, $env, $input);

        /** @var CliProcess $cliProcess Set by the previous method. */
        $cliProcess = $this->lastCliProcess;

        $output = $cliProcess->getOutput();

        if ($splitCallback) {
            return $splitCallback($output);
        }

        return explode(PHP_EOL, $output);
    }

    /**
     * Returns the output of a wp-cli command as a string.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $adminEmail = $I->cliToString('option get admin_email');
     * // Get the list of active plugins in JSON format, two ways.
     * $activePlugins = $I->cliToString(['plugin', 'list','--status=active', '--format=json']);
     * $activePlugins = $I->cliToString(['option', 'get', 'active_plugins' ,'--format=json']);
     * ```
     * @param array<string> $command The string of command and parameters as it would be passed to wp-cli
     *                                          minus `wp`.
     *                                          For back-compatibility purposes you can still pass the commandline as a
     *                                          string, but the array format is the preferred and supported method.
     * @param array<string,string|false>|null $env An array of environment variables to pass to the command.
     * @param mixed $input The input to pass to the command, a stream resource or a \Traversable
     *
     * @return string The output of the command.
     *
     * @throws ModuleException
     */
    public function cliToString(array $command, ?array $env = null, mixed $input = null): string
    {
        $this->cli($command, $env, $input);

        /** @var CliProcess $cliProcess Set by the previous method. */
        $cliProcess = $this->lastCliProcess;

        return $cliProcess->getOutput();
    }

    /**
     * Checks that output from last command contains text.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('option get admin_email');
     * $I->seeInShellOutput('admin@example.org');
     * ```
     *
     * @param string $text The text to assert is in the output.
     *
     * @throws ModuleException
     */
    public function seeInShellOutput(string $text): void
    {
        $this->assertStringContainsString($text, $this->grabLastShellOutput());
    }

    /**
     * Checks that output from last command doesn't contain text.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('plugin list --status=active');
     * $I->dontSeeInShellOutput('my-inactive/plugin.php');
     * ```
     *
     * @param string $text The text to assert is not in the output.
     * @throws ModuleException
     */
    public function dontSeeInShellOutput(string $text): void
    {
        $this->assertStringNotContainsString($text, $this->grabLastShellOutput());
    }

    /**
     * Checks that output from the last command matches a given regular expression.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('option get admin_email');
     * $I->seeShellOutputMatches('/^\S+@\S+$/');
     * ```
     *
     * @param string $regex The regex pattern, including delimiters, to assert the output matches against.
     * @throws ModuleException
     */
    public function seeShellOutputMatches(string $regex): void
    {
        $this->assertRegExp($regex, $this->grabLastShellOutput());
    }

    /**
     * Checks that output from the last command doesn't match a given regular expression.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('option get siteurl');
     * $I->dontSeeShellOutputMatches('/^http/');
     * ```
     *
     * @param string $regex The regex pattern, including delimiters, to assert the output doesn't match against.
     *
     * @throws ModuleException
     */
    public function dontSeeShellOutputMatches(string $regex): void
    {
        $this->assertNotRegExp($regex, $this->grabLastShellOutput());
    }

    /**
     * Checks the result code from the last command.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('option get admin_email');
     * $I->seeResultCodeIs(0);
     * ```
     *
     * @param int $code The desired result code.
     *
     * @throws ModuleException
     */
    public function seeResultCodeIs(int $code): void
    {
        if (!$this->lastCliProcess instanceof CliProcess) {
            throw new ModuleException(
                __CLASS__,
                'No command has run yet: cannot check its exit code.'
            );
        }

        $actual = $this->lastCliProcess->getExitCode();
        $this->assertEquals(
            $code,
            $actual,
            sprintf(
                'The command exited with code %d, expected code %d.',
                $actual,
                $code
            )
        );
    }

    /**
     * Checks the result code from the last command.
     *
     * @example
     * ```php
     * // Return the current site administrator email, using string command format.
     * $I->cli('invalid command');
     * $I->seeResultCodeIsNot(0);
     * ```
     *
     * @param int $code The result code the command should not have exited with.
     *
     * @throws ModuleException If no command has run yet.
     */
    public function seeResultCodeIsNot(int $code): void
    {
        if (!$this->lastCliProcess instanceof CliProcess) {
            throw new ModuleException(
                __CLASS__,
                'No command has run yet: cannot check its exit code.'
            );
        }

        $actual = $this->lastCliProcess->getExitCode();
        $this->assertNotEquals(
            $code,
            $actual,
            sprintf(
                'The command exited with code %d, but it should not have.',
                $actual
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function validateConfig(): void
    {
        parent::validateConfig();
        $this->validateTimeout();
        $this->validatePath();
    }

    /**
     * @throws ModuleConfigException
     */
    private function validateTimeout(): void
    {
        $timeout = $this->config['timeout'] ?? static::DEFAULT_TIMEOUT;

        if (!(is_numeric($timeout) && $timeout > 0)) {
            throw new ModuleConfigException($this, message: 'Timeout [' . print_r($timeout, true) . '] is not valid.');
        }

        $this->config['timeout'] = (float)$timeout;
    }

    /**
     * @throws ModuleConfigException
     */
    private function validatePath(): void
    {
        if (!(
            $this->config['path']
            && is_dir($this->config['path'])
            && is_file($this->config['path'] . '/wp-load.php')
            && is_file($this->config['path'] . '/wp-settings.php')
            && is_file($this->config['path'] . '/wp-includes/version.php')
        )) {
            throw new ModuleConfigException(
                __CLASS__,
                'Specified path [' . $this->config['path']
                . '] is not a directory containing a WordPress installation.'
            );
        }

        $this->config['path'] = Filesystem::realpath($this->config['path']);
    }

    /**
     * @return array<string,string|false>
     */
    private function getDefaultEnv(): array
    {
        $env = [];

        $cacheDir = $this->config['cache-dir'] ?? codecept_output_dir('wp-cli-cache');
        $env['WP_CLI_CACHE_DIR'] = Filesystem::mkdirp($cacheDir);

        foreach ([
                     'config-path' => 'WP_CLI_CONFIG_PATH',
                     'custom-shell' => 'WP_CLI_CUSTOM_SHELL',
                     'packages-dir' => 'WP_CLI_PACKAGES_DIR',
                 ] as $configKey => $envVar) {
            if (!empty($this->config[$configKey])) {
                $env[$envVar] = $this->config[$configKey];
            }
        }

        // Disable update checks by default.
        $env['WP_CLI_DISABLE_AUTO_CHECK_UPDATE'] = true;

        // Args should be treated as strict by default.
        $env['WP_CLI_STRICT_ARGS_MODE'] = '1';

        return $env;
    }

    /**
     * @return array<string>
     */
    private function getOptionsFromConfig(): array
    {
        $options = [];

        foreach ([
                     'skip-plugins',
                     'skip-themes',
                     'skip-packages',
                     'debug',
                     'quiet',
                 ] as $configOption) {
            if (!empty($this->config[$configOption])) {
                $options[] = '--' . $configOption;
            }
        }

        if (isset($this->config['context'])) {
            $options[] = '--context=' . $this->config['context'];
        }

        if (isset($this->config['require'])) {
            foreach ((array)$this->config['require'] as $requireFile) {
                $options[] = '--require=' . $requireFile;
            }
        }

        if (isset($this->config['exec'])) {
            foreach ((array)$this->config['exec'] as $execCode) {
                $options[] = '--exec=' . $execCode;
            }
        }

        if (isset($this->config['user'])) {
            $options['--user'] = $this->config['user'];
        }

        if (isset($this->config['color'])) {
            if ($this->config['color']) {
                $options[] = '--color';
            } else {
                $options[] = '--no-color';
            }
        }

        return $options;
    }

    /**
     * @param array<string> $command
     *
     * @return array<string>
     */
    private function addStrictOptionsFromConfig(array $command): array
    {
        $prepend = [];
        foreach ($this->getOptionsFromConfig() as $cliOptionFromConfig) {
            if (!in_array($cliOptionFromConfig, $command, true)) {
                $prepend[] = $cliOptionFromConfig;
            }
        }
        if (count($prepend)) {
            array_unshift($command, ...$prepend);
        }
        return $command;
    }
}
