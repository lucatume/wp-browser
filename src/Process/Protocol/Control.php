<?php

namespace lucatume\WPBrowser\Process\Protocol;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use lucatume\WPBrowser\Polyfills\Dotenv\Dotenv;

class Control
{
    /**
     * @var array{
     *     autoloadFile: ?string,
     *     requireFiles: string[],
     *     cwd: string,
     *     codeceptionRootDir: string ,
     *     codeceptionConfig: array<string, mixed>,
     *     composerAutoloadPath: ?string,
     *     composerBinDir: ?string,
     *     env: array<string, string|int|float|bool>
     * }
     */
    private $control;

    /**
     * @param array{
     *     autoloadFile?: ?string,
     *     requireFiles?: string[],
     *     cwd?: string|false,
     *     codeceptionRootDir?: string,
     *     codeceptionConfig?: array<string, mixed>,
     *     composerAutoloadPath?: ?string,
     *     composerBinDir?: ?string,
     *     env?: array<string, string|int|float|bool>
     * } $controlArray
     *
     * @throws ConfigurationException
     */
    public function __construct(array $controlArray)
    {
        if (!isset($controlArray['codeceptionConfig'])) {
            $codeceptionConfig = class_exists(Configuration::class) && !Configuration::isEmpty() ?
                Configuration::config()
                : [];
        } else {
            $codeceptionConfig = $controlArray['codeceptionConfig'];
        }
        if (!empty($controlArray['cwd'])) {
            $cwd = $controlArray['cwd'];
        } else {
            $cwd = getcwd() ?: codecept_root_dir();
        }
        $this->control = [
            'autoloadFile' => $controlArray['autoloadFile'] ?? $GLOBALS['_composer_autoload_path'] ?? null,
            'requireFiles' => $controlArray['requireFiles'] ?? [],
            'cwd' => $cwd,
            'codeceptionRootDir' => (string)($controlArray['codeceptionRootDir'] ?? codecept_root_dir()),
            'codeceptionConfig' => $codeceptionConfig,
            'composerAutoloadPath' => (string)($controlArray['composerAutoloadPath']
                ?? $GLOBALS['_composer_autoload_path'] ?? null),
            'composerBinDir' => (string)($controlArray['composerBinDir'] ?? $GLOBALS['_composer_bin_dir'] ?? null),
            'env' => $controlArray['env'] ?? $this->getCurrentEnv(),
        ];
    }

    /**
     * @return array{
     *     autoloadFile: ?string,
     *     requireFiles: string[],
     *     cwd: string,
     *     codeceptionRootDir: string ,
     *     codeceptionConfig: array<string, mixed>,
     *     composerAutoloadPath: ?string,
     *     composerBinDir: ?string,
     *     env: array<string, string|int|float|bool>
     * }
     * @throws ConfigurationException
     */
    public static function getDefault(): array
    {
        if (empty($GLOBALS['_composer_autoload_path'])) {
            $composerAutoloadPath = is_readable(getcwd() . '/vendor/autoload.php') ?
                getcwd() . '/vendor/autoload.php'
                : null;
        } else {
            $composerAutoloadPath = $GLOBALS['_composer_autoload_path'];
        }

        return [
            'autoloadFile' => $GLOBALS['_composer_autoload_path'] ?? null,
            'requireFiles' => [],
            'cwd' => getcwd() ?: codecept_root_dir(),
            'codeceptionRootDir' => codecept_root_dir(),
            'codeceptionConfig' => Configuration::isEmpty() ? [] : Configuration::config(),
            'composerAutoloadPath' => $composerAutoloadPath,
            'composerBinDir' => $GLOBALS['_composer_bin_dir'] ?? null,
            'env' => $_ENV,
        ];
    }

    /**
     * @throws ConfigurationException
     * @throws ProtocolException
     */
    public function apply(): void
    {
        $control = $this->control;

        if (!empty($control['composerAutoloadPath'])) {
            if (!is_file($control['composerAutoloadPath'])) {
                $message = 'Composer autoload file not found: ' . $control['composerAutoloadPath'];
                throw new ProtocolException($message, ProtocolException::COMPOSER_AUTOLOAD_FILE_NOT_FOUND);
            }
            require_once $control['composerAutoloadPath'];
            $GLOBALS['_composer_autoload_path'] = $control['composerAutoloadPath'];
        }

        if (!empty($control['composerBinDir'])) {
            if (!is_dir($control['composerBinDir'])) {
                $message = 'Composer bin dir not found: ' . $control['composerBinDir'];
                throw new ProtocolException($message, ProtocolException::COMPOSER_BIN_DIR_NOT_FOUND);
            }

            $GLOBALS['_composer_bin_dir'] = $control['composerBinDir'];
        }

        if (isset($control['autoloadFile'])) {
            if (!is_file($control['autoloadFile'])) {
                $message = 'Autoload file not found: ' . $control['autoloadFile'];
                throw new ProtocolException($message, ProtocolException::AUTLOAD_FILE_NOT_FOUND);
            }

            require_once $control['autoloadFile'];
        }

        if (count($control['requireFiles'])) {
            foreach ($control['requireFiles'] as $file) {
                if (!is_file($file)) {
                    $message = 'Required file not found: ' . $file;
                    throw new ProtocolException($message, ProtocolException::REQUIRED_FILE_NOT_FOUND);
                }
                require_once $file;
            }
        }

        if (!empty($control['codeceptionConfig']) && class_exists(Configuration::class)) {
            if (!empty($control['codeceptionRootDir'])) {
                if (!is_dir($control['codeceptionRootDir'])) {
                    $message = 'Codeception root dir not found: ' . $control['codeceptionRootDir'];
                    throw new ProtocolException($message, ProtocolException::CODECEPTION_ROOT_DIR_NOT_FOUND);
                }
                chdir($control['codeceptionRootDir']);
                $cwd = getcwd();
            }

            Configuration::config();
            Configuration::append($control['codeceptionConfig']);

            if (!empty($cwd)) {
                chdir($cwd);
            }
        }

        if (!empty($control['cwd'])) {
            if (!is_dir($control['cwd'])) {
                $message = 'CWD not found: ' . $control['cwd'];
                throw new ProtocolException($message, ProtocolException::CWD_NOT_FOUND);
            }
            chdir($control['cwd']);
        }

        if (!empty($control['env'])) {
            foreach ($control['env'] as $key => $value) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }

    /**
     * @return array{
     *     autoloadFile: ?string,
     *     requireFiles: string[],
     *     cwd: string,
     *     codeceptionRootDir: string ,
     *     codeceptionConfig: array<string, mixed>,
     *     composerAutoloadPath: ?string,
     *     composerBinDir: ?string,
     *     env: array<string, string|int|float|bool>
     * }
     */
    public function toArray(): array
    {
        return $this->control;
    }

    /**
     * @return array<string, string|int|float|bool>
     */
    private function getCurrentEnv(): array
    {
        $currentEnv = getenv();

        // @phpstan-ignore-next-line $_ENV is not always defined.
        if (isset($_ENV)) {
            $currentEnv = array_merge($currentEnv, $_ENV);
        }

        return $currentEnv;
    }
}
