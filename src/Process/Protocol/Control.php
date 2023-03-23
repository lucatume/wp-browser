<?php

namespace lucatume\WPBrowser\Process\Protocol;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;

class Control
{
    private array $control;

    public function __construct(array $controlArray)
    {
        $config = class_exists(Configuration::class) ? Configuration::config() : [];
        $this->control = array_replace(
            [
                'autoloadFile' => $GLOBALS['_composer_autoload_path'] ?? null,
                'requireFiles' => [],
                'cwd' => getcwd(),
                'codeceptionRootDir' => null,
                'codeceptionConfig' => $config,
                'composerAutoloadPath' => $GLOBALS['_composer_autoload_path'] ?? null,
                'composerBinDir' => $GLOBALS['_composer_bin_dir'] ?? null,
            ],
            $controlArray
        );
    }

    /**
     * @throws ConfigurationException
     * @throws ProtocolException
     */
    public function apply(): void
    {
        $control = $this->control;

        if (isset($control['composerAutoloadPath'])) {
            if (!is_file($control['composerAutoloadPath'])) {
                $message = 'Composer autoload file not found: ' . $control['composerAutoloadPath'];
                throw new ProtocolException($message, ProtocolException::COMPOSER_AUTOLOAD_FILE_NOT_FOUND);
            }
            require_once $control['composerAutoloadPath'];
            $GLOBALS['_composer_autoload_path'] = $control['composerAutoloadPath'];
        }

        if (isset($control['composerBinDir'])) {
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

            if (isset($cwd)) {
                chdir($cwd);
            }
        }

        if (isset($control['cwd'])) {
            if (!is_dir($control['cwd'])) {
                $message = 'CWD not found: ' . $control['cwd'];
                throw new ProtocolException($message, ProtocolException::CWD_NOT_FOUND);
            }
            chdir($control['cwd']);
        }
    }

    public function toArray(): array
    {
        return $this->control;
    }
}
