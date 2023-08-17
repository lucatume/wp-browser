<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

use JsonException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use StdClass;
use Symfony\Component\Process\Process;

class Composer
{
    public const ERR_FILE_NOT_FOUND = 1;
    public const ERR_FILE_UNREADABLE = 2;
    public const ERR_FILE_WRITE_FAILED = 3;
    public const ERR_UPDATE_FAILED = 4;
    const ERR_DECODING_FAILED = 5;
    private string $composerJsonFile;
    /**
     * @var StdClass
     */
    private stdClass $decoded;

    public static function vendorDir(?string $path = null): string
    {
        $vendorDir = dirname(self::autoloadPath());
        return $path ? $vendorDir . DIRECTORY_SEPARATOR . ltrim($path) : $vendorDir;
    }

    public static function autoloadPath(): string
    {
        global $_composer_autoload_path;
        return realpath($_composer_autoload_path) ?: $_composer_autoload_path;
    }

    public static function binDir(?string $path = null): string
    {
        global $_composer_bin_dir;
        $binDir = rtrim($_composer_bin_dir ?? self::vendorDir('/bin'), '\\/');
        return $path ? $binDir . DIRECTORY_SEPARATOR . ltrim($path, '\\/') : $binDir;
    }

    /**
     * @throws RuntimeException|JsonException
     *
     */
    public function __construct(?string $composerJsonFile = null)
    {
        $this->composerJsonFile = $composerJsonFile ?? codecept_root_dir('composer.json');

        if (!(is_string($this->composerJsonFile) && is_readable($this->composerJsonFile))) {
            throw new RuntimeException('Composer file not found.', self::ERR_FILE_NOT_FOUND);
        }

        $json = file_get_contents($this->composerJsonFile);

        if ($json === false) {
            throw new RuntimeException('Composer file not readable.', self::ERR_FILE_UNREADABLE);
        }

        $decoded = json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        if (!$decoded instanceof StdClass) {
            throw new RuntimeException('Composer file decoding failed.', self::ERR_DECODING_FAILED);
        }

        $this->decoded = $decoded;
    }

    /**
     * @param array<string,string> $packages
     */
    public function requireDev(array $packages): void
    {
        if (!isset($this->decoded->{'require-dev'})) {
            $this->decoded->{'require-dev'} = new StdClass();
        }

        foreach ($packages as $package => $constraint) {
            if (isset($this->decoded->{'require-dev'}->{$package})) {
                continue;
            }
            $this->decoded->{'require-dev'}->{$package} = $constraint;
        }
    }

    /**
     * @throws JsonException
     */
    public function update(string $package = null): void
    {
        $this->write();
        $this->runUpdate($package);
    }

    /**
     * @throws RuntimeException|JsonException
     */
    public function write(): int
    {
        $json = $this->getContents();
        $written = file_put_contents($this->composerJsonFile, $json);
        if ($written === false) {
            throw new RuntimeException(
                sprintf('Could not write to file %s.', $this->composerJsonFile),
                self::ERR_FILE_WRITE_FAILED
            );
        }
        return $written;
    }

    /**
     * @throws RuntimeException
     */
    private function runUpdate(string $package = null): void
    {
        $lockFileExists = file_exists(dirname($this->composerJsonFile) . '/composer.lock');
        $command = $package && $lockFileExists ?
            ['composer', 'update', '--no-interaction', $package]
            : ['composer', 'update', '--no-interaction'];
        $process = new Process($command);
        $process->run();
        if ($process->getExitCode() !== 0) {
            $errorOutput = $process->getErrorOutput();
            throw new RuntimeException(
                sprintf('Composer command failed: %s', $errorOutput),
                self::ERR_UPDATE_FAILED
            );
        }
    }

    /**
     * @return stdClass
     */
    public function getDecodedContents(): stdClass
    {
        return $this->decoded;
    }

    /**
     * @throws JsonException
     */
    public function getContents(): string
    {
        $encoded = json_encode($this->decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        /** @var string $encoded */
        return $encoded;
    }

    public function allowPluginsFromPackage(string $package): void
    {
        if (!isset($this->decoded->config)) {
            $this->decoded->config = new StdClass();
        }

        if (!isset($this->decoded->config->{'allow-plugins'})) {
            $this->decoded->config->{'allow-plugins'} = new StdClass();
        }

        $this->decoded->config->{'allow-plugins'}->{$package} = true;
    }
}
