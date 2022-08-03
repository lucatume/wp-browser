<?php
/**
 * Extends the `mikehaertl/php-shellcommand\Command` class to add API to it to wrap it in a Symfony/Process like
 * API.
 *
 * @package tad\WPBrowser\Process
 */

namespace tad\WPBrowser\Process;

use mikehaertl\shellcommand\Command;

use function array_merge;
use function getenv;

class Process extends Command
{
    
    /**
     * Whether the process should inherit the current `$_ENV` or not.
     *
     * @var bool
     */
    protected $inheritEnvironmentVariables;
    
    /**
     * Sets the process output.
     *
     * @param  int|null  $timeout  The process timeout in seconds.
     *
     * @return void
     */
    public function setTimeout($timeout)
    {
        // @phpstan-ignore-next-line
        $this->timeout = $timeout === null ? $timeout : abs($timeout);
    }
    
    /**
     * Sets whether the process should inherit the current `$_ENV` or not.
     *
     * @param  bool  $inheritEnvironmentVariables  Whether the process should inherit the current `$_ENV` or not.
     *
     * @return void
     */
    public function inheritEnvironmentVariables($inheritEnvironmentVariables)
    {
        $this->inheritEnvironmentVariables = (bool)$inheritEnvironmentVariables;
    }
    
    /**
     * Runs the process, throwing an exception if the process fails.
     *
     * @return Process The process that successfully ran.
     *
     * @throws ProcessFailedException If the process execution fails.
     */
    public function mustRun()
    {
        if ($this->execute() === false) {
            throw new ProcessFailedException($this);
        }
        
        return $this;
    }
    
    public function execute()
    {
        $explicit_proc_env = $this->procEnv;
        
        $this->procEnv = $this->buildFullEnv($explicit_proc_env);
        
        try {
            return parent::execute();
        }
        finally {
            // Reset in case this process runs several times.
            $this->procEnv = $explicit_proc_env;
        }
    }
    
    /**
     * Clones the current instance and returns a new one for the specified command.
     *
     * @param  string|array<string>  $command  The command to run.
     *
     * @return Process A cloned instance of this process to run the specified command.
     */
    public function withCommand($command)
    {
        if (is_array($command)) {
            $command = implode(' ', $command);
        }
        $clone = clone $this;
        
        return $clone->setCommand($command);
    }
    
    /**
     * Returns an instance of this process with the modified environment.
     *
     * @param  array<string,mixed>  $env  The new process environment.
     *
     * @return Process A clone of the current process with the set environment.
     */
    public function withEnv(array $env = [])
    {
        $clone = clone $this;
        $clone->procEnv = $env;
        return $clone;
    }
    
    /**
     * Returns the current process environment variables.
     *
     * @return array<string,mixed>The current Process environment variables.
     */
    public function getEnv()
    {
        return (array)$this->procEnv;
    }
    
    /**
     * Builds and returns a new instance of the process with the specified working directory.
     *
     * @param  string  $cwd  The process working directory.
     *
     * @return Process A clone of the current process with the specified working directory set.
     */
    public function withCwd($cwd)
    {
        $clone = clone $this;
        $clone->procCwd = $cwd;
        
        return $clone;
    }
    
    /**
     * Returns whether the process was successful (exit 0) or not.
     *
     * @return bool Whether the process was successful (exit 0) or not.
     */
    public function isSuccessful()
    {
        return (int)$this->getExitCode() === 0;
    }
    
    /**
     * Returns the process current working directory.
     *
     * @return string|null The process current working directory.
     */
    public function getWorkingDirectory()
    {
        return $this->procCwd;
    }
    
    /**
     * @return bool
     */
    private function isPHP71000OrHigher()
    {
        return PHP_VERSION_ID >= 70100;
    }
    
    /**
     * @return array|null
     */
    private function buildFullEnv(array $explicit_env = null)
    {
        /*
         * 1 Case: The current env should not be inherited.
         *
         * For that to work with proc_open in parent::execute() we need to
         * pass an array. The default is NULL which means everything
         * is inherited.
         *
         * Only the local environment is inherited, meaning that WP_BROWSER_HOST_REQUEST
         * musst be passed explicitly.
         */
        if (!$this->inheritEnvironmentVariables) {
            return (array)$explicit_env;
        }
        /*
         * 2 Case: explicit_env is empty which means no custom env vars were
         * set for this process instance.
         *
         * In that case we want to use the global environment.
         */
        if (empty($explicit_env)) {
            /*
             * On PHP < 7.1 we can't call getenv() to get all env vars.
             * This means we cant merge it with $_ENV either.
             * In this case we return NULL to signal to proc_open
             * that the current env should be used (which is read from the putenv() value store).
             *
             * $_ENV is NOT passed to the child process. It's impossible.
             */
            if ( ! $this->isPHP71000OrHigher()) {
                return null;
            }
            /*
             * On PHP >=7.1 we can merge $_ENV and getenv() so that also
             * $_ENV variables are inherited and not only those set by putenv
             */
            return array_merge($_ENV, getenv());
        }
        /*
         * 3. Case: The user wants to give this process additional env args.
         *
         * We have to merge the current env with the explicitly passed env vars.
         * This was previously broken with wp-browser.
         */
        if ( ! $this->isPHP71000OrHigher()) {
            /*
             * FOR PHP < 7.1 this is the best we can do.
             *
             * This ensures that WPBROWSER_HOST_REQUEST is still set in the child process.
             *
             * However values set by users with putenv() are not passed. But this
             * was broken previously anyway.
             */
            return array_merge($_ENV, $explicit_env);
        }
        return array_merge($_ENV, getenv(), $explicit_env);
    }
    
}
