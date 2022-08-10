<?php
/**
 * Dotenv\Dotenv polyfill to avoid back-compatibility issues with most recent versions of wp-browser.
 *
 * @package lucatume\WPBrowser\Polyfills\Dotenv
 */

namespace lucatume\WPBrowser\Polyfills\Dotenv;

use function lucatume\WPBrowser\envFile;
use function lucatume\WPBrowser\loadEnvMap;

/**
 * Class Dotenv
 *
 * @package lucatume\WPBrowser\Polyfills\Dotenv
 */
class Dotenv
{
    /**
     * The name of the env file to load.
     *
     * @var string
     */
    protected $envFile = '.env';

    /**
     * The absolute path to the root directory to load the env file from.
     *
     * @var string
     */
    protected $rootDir;

    /**
     * The absolute path to the env file to load.
     */
    protected string $envFilePath;

    /**
     * Dotenv constructor.
     *
     * @param string $rootDir The absolute path to the directory to load the env file from.
     * @param string $envFile The basename of the env file to load.
     */
    public function __construct($rootDir, $envFile = '.env')
    {
        $this->envFilePath = $this->getEnvFilePath($rootDir, $envFile);

        if (! file_exists($this->envFilePath)) {
            throw new \InvalidArgumentException("File {$this->envFilePath} does not exist.");
        }

        $this->rootDir = $rootDir;
        $this->envFile = $envFile;
    }

    /**
     * Returns the full path to the env file to load.
     *
     * @param string $rootDir The absolute path to the directory that contains the env file.
     * @param string $envFile The basename of the env file to load.
     *
     * @return string The absolute path to the environment file to load.
     */
    protected function getEnvFilePath($rootDir, $envFile)
    {
        return rtrim($rootDir, '\\/') . '/' . trim($envFile, '\\/');
    }

    /**
     * Loads the env file contents in `getenv()`, `$_ENV` and `$_SERVER`.
     */
    public function load(): void
    {
        loadEnvMap(envFile($this->envFilePath), false);
    }
}
