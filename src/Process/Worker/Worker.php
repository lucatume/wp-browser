<?php

namespace lucatume\WPBrowser\Process\Worker;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use lucatume\WPBrowser\Utils\Composer;

class Worker implements WorkerInterface
{
    /**
     * @var callable
     */
    private $callable;
    /**
     * @var array{autoloadFile: string, requireFiles: string[], cwd: string|false, codeceptionRootDir: string ,codeceptionConfig: array<string, mixed>, composerAutoloadPath: string|null, composerBinDir: string|null}
     */
    private array $control;

    /**
     * @param string[] $requiredResourcesIds
     * @param array<string,mixed> $control
     * @throws ConfigurationException
     */
    public function __construct(
        private string $id,
        callable $callable,
        private array $requiredResourcesIds = [],
        array $control = []
    ) {
        $this->callable = $callable;
        $this->control = array_replace($this->getDefaultControl(), $control);
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array<string>
     */
    public function getRequiredResourcesIds(): array
    {
        return $this->requiredResourcesIds;
    }

    /**
     * @return array{autoloadFile: string, requireFiles: string[], cwd: string|false, codeceptionRootDir: string ,codeceptionConfig: array<string, mixed>, composerAutoloadPath: string|null, composerBinDir: string|null}
     */
    public function getControl(): array
    {
        return $this->control;
    }

    /**
     * @return array{autoloadFile: string, requireFiles: string[], cwd: string|false, codeceptionRootDir: string ,codeceptionConfig: array<string, mixed>, composerAutoloadPath: string|null, composerBinDir: string|null}
     * @throws ConfigurationException
     */
    private function getDefaultControl(): array
    {
        return [
            'autoloadFile' => Composer::autoloadPath(),
            'requireFiles' => [],
            'cwd' => getcwd(),
            'codeceptionRootDir' => codecept_root_dir(),
            'codeceptionConfig' => Configuration::config(),
            'composerAutoloadPath' => $GLOBALS['_composer_autoload_path'] ?? null,
            'composerBinDir' => $GLOBALS['_composer_bin_dir'] ?? null,
        ];
    }

    /**
     * @param array<string> $requireFiles
     */
    public function setRequireFiles(array $requireFiles): self
    {
        $this->control['requireFiles'] = $requireFiles;
        return $this;
    }

    public function setCwd(string $cwd): self
    {
        $this->control['cwd'] = $cwd;
        return $this;
    }
}
