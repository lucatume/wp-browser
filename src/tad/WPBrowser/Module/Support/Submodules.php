<?php

namespace tad\WPBrowser\Module\Support;


use Codeception\Lib\ModuleContainer;
use Codeception\Module;

class Submodules
{
    /**
     * @var array
     */
    protected $modules;
    /**
     * @var ModuleContainer
     */
    protected $moduleContainer;
    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $modulesConfig = [];

    /**
     * @var array
     */
    protected $initializedModules = [];

    public function __construct(array $modules, ModuleContainer $moduleContainer, array $config)
    {
        $this->ensureCodeceptionModules($modules);
        $this->modules = $this->setModuleNames($modules);
        $this->moduleContainer = $moduleContainer;
        $this->config = $config;
    }

    private function ensureCodeceptionModules($modules)
    {
        foreach ($modules as $module) {
            if (!is_a($module, '\\Codeception\\Module', true)) {
                throw new \InvalidArgumentException('Module [' . $module . '] is not a Codeception\\Module');
            }
        }
    }

    private function setModuleNames($modules)
    {
        $moduleNames = [];
        array_map(function ($module) use (&$moduleNames) {
            $moduleNames[(new \ReflectionClass($module))->getShortName()] = $module;
        }, $modules);

        return $moduleNames;
    }

    /**
     * @param string $module
     * @return \Codeception\Module
     */
    public function initializeModule($module, array $setupMethods = [])
    {
        if (!isset($this->modules[$module])) {
            throw new \RuntimeException('Module [' . $module . '] is not registered.');
        }

        if (isset($this->initializedModules[$module])) {
            return $this->initializedModules[$module];
        }

        $class = $this->modules[$module];
        /** @var Module $moduleInstance */
        $moduleInstance = new $class($this->moduleContainer, $this->getModuleConfig($module));
        $moduleInstance->_initialize();
        if (!empty($setupMethods)) {
            foreach ($setupMethods as $setupMethod) {
                call_user_func([$moduleInstance,$setupMethod]);
            }
        }
        $this->initializedModules[$module] = $moduleInstance;

        return $moduleInstance;
    }

    public function isInitializedModule($module)
    {
        return isset($this->initializedModules[$module]);
    }

    /**
     * @param $module
     * @return array|mixed
     */
    private function getModuleConfig($module)
    {
        return isset($this->modulesConfig[$module]) ? $this->modulesConfig[$module] : [];
    }

    public function addModuleConfig($module, array $config)
    {
        $this->modulesConfig[$module] = $config;
    }
}