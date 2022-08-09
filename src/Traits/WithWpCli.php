<?php
/**
 * Provides methods to interact with wp-cli binaries and files.
 *
 * @package lucatume\WPBrowser\Traits
 */

namespace lucatume\WPBrowser\Traits;

use lucatume\WPBrowser\Exceptions\WpCliException;
use lucatume\WPBrowser\Process\Process;
use lucatume\WPBrowser\Process\ProcessFailedException;
use ReflectionClass;
use ReflectionException;
use Server_Command;
use WP_CLI\Configurator;
use function lucatume\WPBrowser\Traits\count;

/**
 * Class WithWpCli
 *
 * @package lucatume\WPBrowser\Traits
 */
trait WithWpCli
{
    /**
     * The absolute path to the wp-cli package root directory.
     *
     * @var string|null
     */
    protected ?string $wpCliRootDir = null;

    /**
     * The absolute path to the WordPress installation root folder.
     *
     * @var string|null
     */
    protected ?string $wpCliWpRootDir = null;

    /**
     * The process adapter the implementation will use.
     *
     * @var Process|null
     */
    protected ?Process $wpCliProcess = null;

    /**
     * Sets up the wp-cli handler in a specific directory.
     *
     * @param string $wpRootFolderDir The absolute path to the WordPress installation root directory.
     * @param Process|null $process         The process wrapper instance to use.
     *
     * @return self This object instance.
     *
     * @throws \lucatume\WPBrowser\Exceptions\WpCliException If wp-cli package files cannot be located while requiring them.
     */
    protected function setUpWpCli(string $wpRootFolderDir, Process $process = null): static
    {
        $this->requireWpCliFiles();
        $this->wpCliWpRootDir = $wpRootFolderDir;
        $this->wpCliProcess = $process ?: new Process();

        return $this;
    }

    /**
     * Requires some wp-cli package files that could not be autoloaded.
     *
     * @return void
     *
     * @throws WpCliException If wp-cli package files cannot be located.
     */
    protected function requireWpCliFiles(): void
    {
        if (!defined('WP_CLI_ROOT')) {
            define('WP_CLI_ROOT', $this->getWpCliRootDir());
        }
        require_once $this->getWpCliRootDir('/php/utils.php');
        require_once $this->getWpCliRootDir('/php/class-wp-cli.php');
        require_once $this->getWpCliRootDir('/php/class-wp-cli-command.php');
    }

    /**
     * Returns the absolute path to the wp-cli package root directory.
     *
     * @param string|null $path A path to append to the root directory.
     *
     * @return string The absolute path to the wp-cli package root directory.
     *
     * @throws \lucatume\WPBrowser\Exceptions\WpCliException If the path to the WP_CLI\Configurator class cannot be resolved.
     */
    protected function getWpCliRootDir(string $path = null): string
    {
        if ($this->wpCliRootDir === null) {
            try {
                $ref = new ReflectionClass(Configurator::class);
            } catch (ReflectionException $e) {
                throw WpCliException::becauseConfiguratorClassCannotBeFound();
            }

            $filename     = $ref->getFileName();

            if ($filename === false) {
                throw new WpCliException('Filename could not be read from reflection.');
            }

            $wpCliRootDir = dirname($filename) . '/../../';

            $wpCliRootRealPath = realpath($wpCliRootDir);

            if (!empty($wpCliRootRealPath)) {
                $wpCliRootDir = $wpCliRootRealPath;
            }

            $this->wpCliRootDir = $wpCliRootDir;
        }

        return $path ?
            rtrim($this->wpCliRootDir, '\\/') . DIRECTORY_SEPARATOR . ltrim($path, '\\/')
            : $this->wpCliRootDir;
    }

    /**
     * Formats an associative array of options to be used as wp-cli options.
     *
     * @param array<string,string|int|float|bool> $options The array of wp-cli options to format.
     *
     * @return array<string> The formatted array of wp-cli options, in the `[ --<key> <value> ]` format.
     */
    protected function wpCliOptions(array $options): array
    {
        $formatted = [];

        foreach ($options as $key => $value) {
            if ($value !== true) {
                // Normal options.
                $formatted [] = '--' . ltrim($key, '-') . '=' . $value;
            } else {
                // Flag options.
                $formatted [] = '--' . ltrim($key, '-');
            }
        }

        return $formatted;
    }

    /**
     * Parses the inline options found in a command and returns them in an associative array.
     *
     * @param string|array<string> $command The command to parse
     *
     * @return array<int|string,mixed> An associative array of all the options found in the command.
     */
    protected function parseWpCliInlineOptions(array|string $command): array
    {
        $parsed = [];

        foreach ((array)$command as $c) {
            $pattern = '/--(?<key>[^=]*?)=(?<value>([\'"]{1}.*?[\'"]{1})|.*?)(?=(\\s+|$))/um';
            preg_match_all($pattern, $c, $matches);
            $keys = isset($matches['key']) ? (array)$matches['key'] : [];
            $values = isset($matches['value']) ? (array)$matches['value'] : [];
            $parsed[] = array_combine($keys, $values);
        }

        $parsed = array_filter($parsed);

        return count($parsed) ? array_merge(...array_filter($parsed)) : [];
    }

    /**
     * Updates an option in the database.
     *
     * @param string $name     The option name.
     * @param string $value    The option value.
     * @param string $autoload One of `yes` or `no` to indicate if the option should be auto-loaded by WordPress or not.
     * @param string $format   The option serialization format, one of `plaintext` or `json`.
     *
     * @return void
     *
     * @throws \lucatume\WPBrowser\Exceptions\WpCliException If the option update command fails.
     */
    protected function updateOptionWithWpcli(string $name, string $value, string $autoload = 'yes', string $format = 'plaintext'): void
    {
        if (!$this->wpCliWpRootDir) {
            throw WpCliException::becauseCommandRequiresSetUp();
        }

        $autoloadOption = "--autoload={$autoload}";
        $formatOption = "--format={$format}";

        $command = ['option', 'update', $name, $value, $autoloadOption, $formatOption];

        codecept_debug('Updating WordPress option with command: ' . json_encode($command));

        $set = $this->executeWpCliCommand($command);

        codecept_debug($set->getOutput());

        if ($set->getExitCode() !== 0) {
            throw WpCliException::becauseACommandFailed($set);
        }
    }

    /**
     * Executes a wp-cli command.
     *
     * @param array<string> $command The command fragments; a mix of arguments and options.
     * @param int|null $timeout The timeout, in seconds, to use for the command. Use `null` to remove the timeout.
     * @param array<string,string|int|float> $env An optional,associative array of environment variables to set for
     *                                                the process.
     *
     * @return Process The process object that executed the command.
     *
     * @throws WpCliException If the wp-cli boot file path cannot be found.
     */
    protected function executeWpCliCommand(array $command = ['version'], ?int $timeout = 60, array $env = []): Process
    {
        $fullCommand   = $this->buildFullCommand(array_merge(['--path=' . $this->wpCliWpRootDir], $command));
        $process       = $this->wpCliProcess->withCommand($fullCommand)->withCwd($this->wpCliWpRootDir);
        $process->setTimeout($timeout);
        $process->inheritEnvironmentVariables(true);
        if (count($env)) {
            $currentEnv = (array)$process->getEnv();
            $process = $process->withEnv(array_merge($currentEnv, $env));
        }

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            // Even if the process did not complete successfully, go on.
            codecept_debug('WPCLI Process failed: ' . $e->getMessage());
        }

        return $process;
    }

    /**
     * Builds the full command to run including the PHP binary and the wp-cli boot file path.
     *
     * @example
          * ```php
     * // This method is defined in the WithWpCli trait.
     * // Set the wp-cli path, `$this` is a test case.
     * $this->setUpWpCli( '/var/www/html' );
     * // Builds the full wp-cli command, including the `path` variable.
     * $fullCommand =  $this->buildFullCommand(['core', 'version']);
     * // The full command can then be used to run it with another process handler.
     * $wpCliProcess = new Process($fullCommand);
     * $wpCliProcess->run();
     * ```
     *@param string|array<string> $command The command to run.
     *
     * @return array<string> The full command including the current PHP binary and the absolute path to the wp-cli boot
     *                       file.
     *
     * @throws WpCliException If there's an issue building the command.
     *
     */
    public function buildFullCommand(array|string $command): array
    {
        $fullCommand = array_merge([
            PHP_BINARY,
            $this->getWpCliBootFilePath(),
        ], (array)$command);

        if (!empty($fullCommand) && count($fullCommand) > 0) {
            $fullCommand[0] = escapeshellarg($fullCommand[0]);
        }

        return $fullCommand;
    }

    /**
     * Returns the absolute path the the wp-cli boot file.
     *
     * @return string The absolute path the the wp-cli boot file.
     *
     * @throws \lucatume\WPBrowser\Exceptions\WpCliException If the path to the WP_CLI\Configurator class cannot be resolved.
     */
    protected function getWpCliBootFilePath(): string
    {
        return $this->getWpCliRootDir('/php/boot-fs.php');
    }

    /**
     * Returns the absolute path to the wp-cli server command router file, part of the `wp-cli/server-command` package.
     *
     * @return string The absolute path to the wp-cli server command router file.
     * @throws WpCliException If the `\Server_Command` class cannot  be autoloaded or the router file was not found.
     */
    protected function getWpCliRouterFilePath(): string
    {
        try {
            $serverCommandFile = (new ReflectionClass(Server_Command::class))->getFileName();
            if ($serverCommandFile === false) {
                throw WpCliException::becauseServerCommandClassWasNotFound();
            }
            $routerFilePath = dirname($serverCommandFile, 2) . '/router.php';
            if (!file_exists($routerFilePath)) {
                throw WpCliException::becauseRouterFileWasNotFound($routerFilePath);
            }
        } catch (ReflectionException $e) {
            throw WpCliException::becauseServerCommandClassWasNotFound();
        }

        return $routerFilePath;
    }
}