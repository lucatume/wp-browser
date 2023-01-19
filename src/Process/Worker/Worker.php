<?php

namespace lucatume\WPBrowser\Process\Worker;

use lucatume\WPBrowser\Utils\Composer;

class Worker implements WorkerInterface
{
    /**
     * @var $callable
     */
    private $callable;
    private string $id;
    /**
     * @var array<string>
     */
    private array $requiredResourcesIds;
    /**
     * @var array<string,string>
     */
    private array $control;

    public function __construct(string $id, callable $callable, array $requiredResourcesIds = [], array $control = [])
    {
        $this->id = $id;
        $this->callable = $callable;
        $this->requiredResourcesIds = $requiredResourcesIds;
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
     * @return array<string,string>
     */
    public function getControl(): array
    {
        return $this->control;
    }

    /**
     * @return array<string,string>
     */
    private function getDefaultControl(): array
    {
        return [
            'autoloadFile' => Composer::autoloadPath(),
            'returnValueSeparator' => md5(uniqid('sep_', true))
        ];
    }
}