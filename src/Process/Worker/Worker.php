<?php

namespace lucatume\WPBrowser\Process\Worker;

use Closure;
use Codeception\Exception\ConfigurationException;
use lucatume\WPBrowser\Process\Protocol\Control;
use ReflectionFunction;

class Worker implements WorkerInterface
{
    /**
     * @var callable
     */
    private $callable;
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
    private array $control;

    /**
     * @param string[] $requiredResourcesIds
     * @param array{
     *     autoloadFile?: ?string,
     *     requireFiles?: string[]|null,
     *     cwd?: string|false|null,
     *     codeceptionRootDir?: ?string,
     *     codeceptionConfig?: array<string, mixed>|null,
     *     composerAutoloadPath?: ?string,
     *     composerBinDir?: ?string,
     *     env?: array<string, string|int|float|bool>
     * } $control
     * @throws ConfigurationException
     */
    public function __construct(
        private string $id,
        callable $callable,
        private array $requiredResourcesIds = [],
        array $control = []
    ) {
        $this->callable = $callable;
        $defaultControl = Control::getDefault();
        if (!empty($control['cwd'])) {
            $cwd = $control['cwd'];
        } else {
            $cwd = getcwd() ?: codecept_root_dir();
        }

        if ($callable instanceof Closure) {
            // Closures might come from files that are not autoloaded (e.g. test cases); include them in the required
            // files to make sure the Closure will be bound to a valid scope.
            $closureFile = (new ReflectionFunction($callable))->getFileName();
            if ($closureFile !== false) {
                if (!isset($control['requireFiles'])) {
                    $control['requireFiles'] = [];
                }
                $control['requireFiles'][] = $closureFile;
                $control['requireFiles'] = array_values(array_unique($control['requireFiles']));
            }
        }

        $this->control = [
            'autoloadFile' => $control['autoloadFile'] ?? $defaultControl['autoloadFile'],
            'requireFiles' => $control['requireFiles'] ?? $defaultControl['requireFiles'],
            'cwd' => $cwd,
            'codeceptionRootDir' => $control['codeceptionRootDir'] ?? $defaultControl['codeceptionRootDir'],
            'codeceptionConfig' => $control['codeceptionConfig'] ?? $defaultControl['codeceptionConfig'],
            'composerAutoloadPath' => $control['composerAutoloadPath'] ?? $defaultControl['composerAutoloadPath'],
            'composerBinDir' => $control['composerBinDir'] ?? $defaultControl['composerBinDir'],
            'env' => $control['env'] ?? $defaultControl['env'],
        ];
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
    public function getControl(): array
    {
        return $this->control;
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
