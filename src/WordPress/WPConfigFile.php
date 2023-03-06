<?php

namespace lucatume\WPBrowser\WordPress;

use Exception;
use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\SerializableThrowable;
use lucatume\WPBrowser\Utils\MonkeyPatch;
use lucatume\WPBrowser\WordPress\Traits\WordPressChecks;

class WPConfigFile
{
    use WordPressChecks;

    private string $wpConfigFilePath;
    private string $wpSettingsFilePath;
    private array $constants = [];
    private array $variables = [];

    /**
     * @throws InstallationException|ProcessException
     */
    public function __construct(string $wpRootDir, string $wpConfigFilePath)
    {
        $this->checkWPRootDir($wpRootDir);

        if (!is_file($wpConfigFilePath)) {
            throw new InstallationException(
                "Cannot read multisite constants: wp-config.php file missing.",
                InstallationException::WP_CONFIG_FILE_NOT_FOUND
            );
        }

        $this->wpConfigFilePath = $wpConfigFilePath;

        $wpSettingsFilePath = rtrim($wpRootDir, '\\/') . '/wp-settings.php';

        if (!is_file($wpSettingsFilePath)) {
            throw new InstallationException(
                'Could not find the wp-settings.php file.',
                InstallationException::WP_SETTINGS_FILE_NOT_FOUND
            );
        }

        $this->wpSettingsFilePath = $wpSettingsFilePath;
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

    public function getConstant(string $constant): mixed
    {
        return $this->constants[$constant] ?? null;
    }

    public function getConstants():array{
        return $this->constants;
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
        $wpConfigFile = $this->wpConfigFilePath;
        $wpSettingsFile = $this->wpSettingsFilePath;

        try {
            $result = Loop::executeClosure(static function () use ($wpSettingsFile, $wpConfigFile): array {
                // Include the wp-config.php file, but do not include the wp-settings.php file.
                MonkeyPatch::redirectFileToFile($wpSettingsFile, MonkeyPatch::dudFile());
                $definedConstantsBefore = get_defined_constants(true)['user'] ?? [];
                include $wpConfigFile;

                $constants= array_diff_key(get_defined_constants(true)['user'] ?? [], $definedConstantsBefore);
                $variables = get_defined_vars();
                unset(
                    $variables['constants'],
                    $variables['wpSettingsFile'],
                    $variables['wpConfigFile'],
                    $variables['definedConstantsBefore']
                );

                return ['constants' => $constants, 'variables' => $variables];
            });

            $returnValue = $result->getReturnValue();

            if ($result->getExitCode() !== 0) {
                if ($returnValue instanceof SerializableThrowable) {
                    throw $returnValue;
                }
                throw new ProcessException($result->getStderrBuffer());
            }

            $values = $returnValue;

            if (!(is_array($values) && isset($values['constants'], $values['variables']))) {
                throw new ProcessException("Inclusion of wp-config file {$this->wpConfigFilePath} did not return expected values.");
            }

            $this->constants = $values['constants'];
            $this->variables = $values['variables'];
        } catch (Exception $e) {
            throw new ProcessException(
                'Could not parse the wp-config.php file: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function getVar(string $varName): mixed
    {
        return $this->variables[$varName] ?? null;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getFilePath(): string
    {
        return $this->wpConfigFilePath;
    }

    /**
     * @throws WpConfigFileException
     */
    public function getConstantOrThrow(string $string)
    {
        if (!isset($this->constants[$string])) {
            throw new WpConfigFileException("Constant {$string} not defined.",
                WpConfigFileException::CONSTANT_UNDEFINED);
        }

        return $this->constants[$string];
    }

    /**
     * @throws WpConfigFileException
     */
    public function getVariableOrThrow(string $string)
    {
        if (!isset($this->variables[$string])) {
            throw new WpConfigFileException("Variable {$string} not defined.",
                WpConfigFileException::VARIABLE_UNDEFINED);
        }

        return $this->variables[$string];
    }
}
