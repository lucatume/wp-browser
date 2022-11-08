<?php

namespace lucatume\WPBrowser\WordPress;

use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use lucatume\WPBrowser\Utils\WP;
use lucatume\WPBrowser\WordPress\Traits\WordPressFiles;

class WpConfigInclude
{
    private static array $cache = [];
    private string $rootDir;
    private string $wpConfigFile;
    private string $wpSettingsFile;
    private array $constants = [];
    private array $variables = [];

    /**
     * @throws InstallationException
     * @throws ProcessException
     */
    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
        $this->wpConfigFile = WP::findWpConfigFileOrThrow($this->rootDir);
        $this->wpSettingsFile = WP::findWpSettingsFileOrThrow($this->rootDir);
        $this->includeFile();
    }

    public function isDefinedConst(string ...$constants): bool
    {
        return count(array_intersect(array_keys($this->constants), $constants)) === count($constants);
    }

    public function issetVar(string $varName): bool
    {
        return isset($this->variables[$varName]);
    }

    public function getConstant(string $constant): string|int|float|bool|null
    {
        return $this->constants[$constant] ?? null;
    }

    public function getVariable(string $varName): mixed
    {
        return $this->variables[$varName] ?? null;
    }

    /**
     * @throws ProcessException
     */
    private function includeFile(): void
    {
        if (isset(self::$cache[$this->wpConfigFile])) {
            $this->constants = self::$cache[$this->wpConfigFile]['constants'];
            $this->variables = self::$cache[$this->wpConfigFile]['variables'];

            return;
        }

        $wpConfigFile = $this->wpConfigFile;
        $wpSettingsFile = $this->wpSettingsFile;

        try {
            $result = Loop::executeClosureOrThrow(static function () use ($wpSettingsFile, $wpConfigFile): array {
                // Include the wp-config.php file, but do not include the wp-settings.php file.
                MonkeyPatch::redirectFileToFile($wpSettingsFile, MonkeyPatch::dudFile());
                include $wpConfigFile;

                $constants = get_defined_constants(true)['user'] ?? [];
                $variables = get_defined_vars();
                unset($variables['constants'], $variables['wpSettingsFile'], $variables['wpConfigFile']);

                return ['constants' => $constants, 'variables' => $variables];
            });

            $values = $result->getReturnValue();

            if (!is_array($values) && isset($values['constants'], $values['variables'])) {
                throw new ProcessException("Inclusiong if wp-config file $wpConfigFile did not return expected values.");
            }

            $this->constants = $values['constants'];
            $this->variables = $values['variables'];
            self::$cache[$this->wpConfigFile] = $values;
        } catch (ProcessException $e) {
            throw new ProcessException(
                'Could not parse the wp-config.php file: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        self::$cache[$this->wpConfigFile] = $values;
    }

    public function isDefinedAnyConst(string ...$constants): bool
    {
        foreach ($constants as $constant) {
            if ($this->isDefinedConst($constant)) {
                return true;
            }
        }

        return false;
    }
}
