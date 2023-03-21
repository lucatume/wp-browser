<?php

namespace lucatume\WPBrowser\Process\Protocol;

use Codeception\Configuration;

class Control
{
    private array $control;

    public function __construct(array $controlArray)
    {
        $config = class_exists(Configuration::class) ? Configuration::config() : [];
        $this->control = array_replace(
            [
                'autoloadFile' => $GLOBALS['__composer_autoload_file'] ?? null,
                'requireFiles' => [],
                'cwd' => getcwd(),
                'codeceptionConfig' => $config,
            ],
            $controlArray
        );
    }

    public function apply(): void
    {
        $control = $this->control;

        if (is_file($control['autoloadFile'])) {
            require_once $control['autoloadFile'];
        }
        if (count($control['requireFiles'])) {
            foreach ($control['requireFiles'] as $file) {
                require_once $file;
            }
        }

        if (isset($control['cwd'])) {
            chdir($control['cwd']);
        }

        if (!empty($control['codeceptionConfig']) && class_exists(Configuration::class)) {
            Configuration::append($control['codeceptionConfig']);
        }
    }
}
