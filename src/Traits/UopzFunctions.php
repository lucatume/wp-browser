<?php

namespace lucatume\WPBrowser\Traits;

use Closure;

trait UopzFunctions
{
    /**
     * @var array<string,bool>
     */
    private static $uopzSetFunctionReturns = [];

    /**
     * @var array<string,bool>
     */
    private static $uopzSetFunctionHooks = [];

    /**
     * @var array<string,mixed>
     */
    private static $uopzSetConstants = [];

    /**
     * @var array<string,bool>
     */
    private static $uopzSetClassMocks = [];

    /**
     * @var array<string,bool>
     */
    private static $uopzUnsetClassFinalAttribute = [];

    /**
     * @var array<string,bool>
     */
    private static $uopzAddClassMethods = [];

    /**
     * @var array<string,bool>
     */
    private static $uopzUnsetClassMethodFinalAttribute = [];

    /**
     * @var array<string,mixed>
     */
    private static $uopzSetObjectProperties = [];

    /**
     * @var array<string,array<string,mixed>>
     */
    private static $uopzSetMethodStaticVariables = [];

    /**
     * @var array<string,array<string,mixed>>
     */
    private static $uopzSetFunctionStaticVariables = [];

    /**
     * @var array<string,bool>
     */
    private static $uopzAddedFunctions = [];

    /**
     * @var bool|null
     */
    private static $uopzAllowExit;

    /**
     * @param mixed $value
     */
    protected function setFunctionReturn(string $function, $value, bool $execute = false): Closure
    {
        if (!function_exists('uopz_set_return')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        uopz_set_return($function, $value, $execute);
        self::$uopzSetFunctionReturns[$function] = true;

        return function () use ($function) {
            $this->unsetFunctionReturn($function);
        };
    }

    protected function unsetFunctionReturn(string $function): void
    {
        if (!isset(self::$uopzSetFunctionReturns[$function])) {
            return;
        }

        uopz_unset_return($function);
        unset(self::$uopzSetFunctionReturns[$function]);
    }

    /**
     * @param mixed $value
     */
    protected function setMethodReturn(string $class, string $method, $value, bool $execute = false): Closure
    {
        $classAndMethod = "$class::$method";
        uopz_set_return($class, $method, $value, $execute);
        self::$uopzSetFunctionReturns[$classAndMethod] = true;

        return function () use ($class, $method) {
            $this->unsetMethodReturn($class, $method);
        };
    }

    protected function unsetMethodReturn(string $class, string $method): void
    {
        $classAndMethod = "$class::$method";

        if (!isset(self::$uopzSetFunctionReturns[$classAndMethod])) {
            return;
        }

        uopz_unset_return($class, $method);
        unset(self::$uopzSetFunctionReturns[$classAndMethod]);
    }

    protected function setFunctionHook(string $function, Closure $hook): Closure
    {
        if (!function_exists('uopz_set_hook')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        uopz_set_hook($function, $hook);
        self::$uopzSetFunctionHooks[$function] = true;

        return function () use ($function) {
            $this->unsetFunctionHook($function);
        };
    }

    protected function unsetFunctionHook(string $function): void
    {
        if (!isset(self::$uopzSetFunctionHooks[$function])) {
            return;
        }

        uopz_unset_hook($function);
        unset(self::$uopzSetFunctionHooks[$function]);
    }

    protected function setMethodHook(string $class, string $method, Closure $hook): Closure
    {
        if (!function_exists('uopz_set_hook')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        $classAndMethod = "$class::$method";
        uopz_set_hook($class, $method, $hook);
        self::$uopzSetFunctionHooks[$classAndMethod] = true;

        return function () use ($class, $method) {
            $this->unsetMethodHook($class, $method);
        };
    }

    protected function unsetMethodHook(string $class, string $method): void
    {
        $classAndMethod = "$class::$method";

        if (!isset(self::$uopzSetFunctionHooks[$classAndMethod])) {
            return;
        }

        uopz_unset_hook($class, $method);
        unset(self::$uopzSetFunctionHooks[$classAndMethod]);
    }

    /**
     * @param mixed $value
     */
    protected function setConstant(string $constant, $value): Closure
    {
        if (!function_exists('uopz_redefine')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        $previousValue = defined($constant) ? constant($constant) : '__NOT_PREVIOUSLY_DEFINED__';
        if ($previousValue === '__NOT_PREVIOUSLY_DEFINED__') {
            define($constant, $value);
        } else {
            uopz_redefine($constant, $value);
        }
        self::$uopzSetConstants[$constant] = $previousValue;

        return function () use ($constant) {
            $this->unsetConstant($constant);
        };
    }

    protected function unsetConstant(string $constant): void
    {
        if (!isset(self::$uopzSetConstants[$constant])) {
            return;
        }

        $previousValue = self::$uopzSetConstants[$constant];

        if ($previousValue !== '__NOT_PREVIOUSLY_DEFINED__') {
            uopz_redefine($constant, $previousValue);
        } else {
            uopz_undefine($constant);
        }
        unset(self::$uopzSetConstants[$constant]);
    }

    /**
     * @param mixed $value
     */
    protected function setClassConstant(string $class, string $constant, $value): Closure
    {
        if (!function_exists('uopz_redefine')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        $previousValue = defined("$class::$constant") ?
            constant("$class::$constant")
            : '__NOT_PREVIOUSLY_DEFINED__';
        uopz_redefine($class, $constant, $value);
        self::$uopzSetConstants["$class::$constant"] = $previousValue;

        return function () use ($class, $constant) {
            $this->unsetClassConstant($class, $constant);
        };
    }

    protected function unsetClassConstant(string $class, string $constant): void
    {
        if (!isset(self::$uopzSetConstants["$class::$constant"])) {
            return;
        }

        $previousValue = self::$uopzSetConstants["$class::$constant"];

        if ($previousValue !== '__NOT_PREVIOUSLY_DEFINED__') {
            uopz_redefine($class, $constant, $previousValue);
        } else {
            uopz_undefine($class, $constant);
        }
        unset(self::$uopzSetConstants["$class::$constant"]);
    }

    /**
     * @param mixed $mock
     */
    protected function setClassMock(string $class, $mock): Closure
    {
        if (!function_exists('uopz_set_mock')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        uopz_set_mock($class, $mock);
        self::$uopzSetClassMocks[$class] = true;

        return function () use ($class) {
            $this->unsetClassMock($class);
        };
    }

    protected function unsetClassMock(string $class): void
    {
        if (!isset(self::$uopzSetClassMocks[$class])) {
            return;
        }

        uopz_unset_mock($class);
        unset(self::$uopzSetClassMocks[$class]);
    }

    protected function unsetClassFinalAttribute(string $class): Closure
    {
        if (!function_exists('uopz_unset_return')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        $flags = uopz_flags($class, '');
        uopz_flags($class, '', $flags & ~ZEND_ACC_FINAL);
        self::$uopzUnsetClassFinalAttribute[$class] = true;

        return function () use ($class) {
            $this->resetClassFinalAttribute($class);
        };
    }

    protected function resetClassFinalAttribute(string $class): void
    {
        if (!isset(self::$uopzUnsetClassFinalAttribute[$class])) {
            return;
        }

        $flags = uopz_flags($class, '');
        uopz_flags($class, '', $flags | ZEND_ACC_FINAL);
        unset(self::$uopzUnsetClassFinalAttribute[$class]);
    }

    protected function unsetMethodFinalAttribute(string $class, string $method): Closure
    {
        if (!function_exists('uopz_unset_return')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        $flags = uopz_flags($class, $method);
        uopz_flags($class, $method, $flags & ~ZEND_ACC_FINAL);
        self::$uopzUnsetClassMethodFinalAttribute["$class::$method"] = true;

        return function () use ($class, $method) {
            $this->resetMethodFinalAttribute($class, $method);
        };
    }

    protected function resetMethodFinalAttribute(string $class, string $method): void
    {
        $classAndMethod = "$class::$method";
        if (!isset(self::$uopzUnsetClassMethodFinalAttribute[$classAndMethod])) {
            return;
        }

        $flags = uopz_flags($class, $method);
        uopz_flags($class, $method, $flags | ZEND_ACC_FINAL);
        unset(self::$uopzUnsetClassMethodFinalAttribute[$classAndMethod]);
    }

    protected function addClassMethod(string $class, string $method, Closure $closure, bool $static = false): Closure
    {
        if (!function_exists('uopz_add_function')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        $flags = ZEND_ACC_PUBLIC;
        if ($static) {
            $flags |= ZEND_ACC_STATIC;
        }
        uopz_add_function($class, $method, $closure, $flags);
        self::$uopzAddClassMethods["$class::$method"] = true;

        return function () use ($class, $method) {
            $this->removeClassMethod($class, $method);
        };
    }

    protected function removeClassMethod(string $class, string $method): void
    {
        $classAndMethod = "$class::$method";
        if (!isset(self::$uopzAddClassMethods[$classAndMethod])) {
            return;
        }

        uopz_del_function($class, $method);
        unset(self::$uopzAddClassMethods[$classAndMethod]);
    }

    /**
     * @param string|object $classOrObject
     * @param mixed $value
     */
    protected function setObjectProperty(
        $classOrObject,
        string $property,
        $value
    ): Closure {
        if (!function_exists('uopz_set_property')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        $previousValue = uopz_get_property($classOrObject, $property);
        uopz_set_property($classOrObject, $property, $value);
        $id = is_string($classOrObject) ? $classOrObject : spl_object_hash($classOrObject);
        self::$uopzSetObjectProperties["$id::$property"] = [$previousValue, $classOrObject];

        return function () use ($classOrObject, $property) {
            $this->resetObjectProperty($classOrObject, $property);
        };
    }

    /**
     * @param string|object $classOrObject
     * @return mixed
     */
    protected function getObjectProperty($classOrObject, string $property)
    {
        if (!function_exists('uopz_get_property')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        return uopz_get_property($classOrObject, $property);
    }

    /**
     * @param string|object $classOrObject
     */
    protected function resetObjectProperty($classOrObject, string $property): void
    {
        $id = is_string($classOrObject) ? $classOrObject : spl_object_hash($classOrObject);

        if (!isset(self::$uopzSetObjectProperties["$id::$property"])) {
            return;
        }

        [$previousValue, $classOrObject] = self::$uopzSetObjectProperties["$id::$property"];
        uopz_set_property($classOrObject, $property, $previousValue);
        unset(self::$uopzSetObjectProperties["$id::$property"]);
    }

    /**
     * @param array<string,mixed> $values
     */
    protected function setMethodStaticVariables(string $class, string $method, array $values): Closure
    {
        if (!function_exists('uopz_set_static')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        $currentValues = uopz_get_static($class, $method);
        $currentValues['__CLONE__'] = '__CLONE__';
        unset($currentValues['__CLONE__']);

        if (!isset(self::$uopzSetMethodStaticVariables["$class::$method"])) {
            self::$uopzSetMethodStaticVariables["$class::$method"] = $currentValues;
        }

        uopz_set_static($class, $method, $values);

        return function () use ($class, $method) {
            $this->resetMethodStaticVariables($class, $method);
        };
    }

    /**
     * @return array<string,mixed>
     */
    protected function getMethodStaticVariables(string $class, string $method): array
    {
        if (!function_exists('uopz_get_static')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        $currentValues = uopz_get_static($class, $method);
        $currentValues['__CLONE__'] = '__CLONE__';
        unset($currentValues['__CLONE__']);

        return $currentValues;
    }

    protected function resetMethodStaticVariables(string $class, string $method): void
    {
        if (!isset(self::$uopzSetMethodStaticVariables["$class::$method"])) {
            return;
        }

        $staticVariables = self::$uopzSetMethodStaticVariables["$class::$method"];
        uopz_set_static($class, $method, $staticVariables);
        unset(self::$uopzSetMethodStaticVariables["$class::$method"]);
    }

    /**
     * @return array<string,mixed>
     */
    protected function getFunctionStaticVariables(string $function): array
    {
        if (!function_exists('uopz_get_static')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        $currentValues = uopz_get_static($function);
        $currentValues['__CLONE__'] = '__CLONE__';
        unset($currentValues['__CLONE__']);

        return $currentValues;
    }

    /**
     * @param array<string,mixed> $values
     */
    protected function setFunctionStaticVariables(string $function, array $values): Closure
    {
        if (!function_exists('uopz_set_static')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        $currentValues = uopz_get_static($function);
        $currentValues['__CLONE__'] = '__CLONE__';
        unset($currentValues['__CLONE__']);

        if (!isset(self::$uopzSetFunctionStaticVariables[$function])) {
            self::$uopzSetFunctionStaticVariables[$function] = $currentValues;
        }

        uopz_set_static($function, array_merge($currentValues, $values));

        return function () use ($function) {
            $this->resetFunctionStaticVariables($function);
        };
    }

    protected function resetFunctionStaticVariables(string $function): void
    {
        if (!isset(self::$uopzSetFunctionStaticVariables[$function])) {
            return;
        }

        $staticVariables = self::$uopzSetFunctionStaticVariables[$function];
        uopz_set_static($function, $staticVariables);
        unset(self::$uopzSetFunctionStaticVariables[$function]);
    }

    protected function addFunction(string $function, Closure $handler): Closure
    {
        if (!function_exists('uopz_add_function')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        self::$uopzAddedFunctions[$function] = true;
        uopz_add_function($function, $handler);

        return function () use ($function) {
            $this->removeFunction($function);
        };
    }

    protected function removeFunction(string $function): void
    {
        if (!function_exists('uopz_del_function')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        if (!isset(self::$uopzAddedFunctions[$function])) {
            return;
        }

        uopz_del_function($function);
        unset(self::$uopzAddedFunctions[$function]);
    }

    protected function preventExit(): void
    {
        if (!function_exists('uopz_allow_exit')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        uopz_allow_exit(false);
        self::$uopzAllowExit = false;
    }

    protected function allowExit(): void
    {
        if (self::$uopzAllowExit === true) {
            return;
        }

        if (!function_exists('uopz_allow_exit')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        uopz_allow_exit(true);
        self::$uopzAllowExit = true;
    }

    /**
     * @after
     */
    public function resetUopzAlterations(): void
    {
        foreach (self::$uopzSetFunctionReturns as $function => $k) {
            if (strpos($function, '::') !== false) {
                $this->unsetMethodReturn(...explode('::', $function));
            } else {
                $this->unsetFunctionReturn($function);
            }
        }

        foreach (self::$uopzSetFunctionHooks as $function => $k) {
            $this->unsetFunctionHook($function);
        }

        foreach (self::$uopzSetConstants as $constant => $k) {
            $this->unsetConstant($constant);
        }

        foreach (self::$uopzSetClassMocks as $class => $k) {
            $this->unsetClassMock($class);
        }

        foreach (self::$uopzSetObjectProperties as $idAndProperty => [$previousValue, $classOrObject]) {
            [, $property] = explode('::', $idAndProperty);
            $this->resetObjectProperty($classOrObject, $property);
        }

        foreach (self::$uopzSetMethodStaticVariables as $classAndMethod => $values) {
            [$class, $method] = explode('::', $classAndMethod);
            $this->resetMethodStaticVariables($class, $method);
        }

        foreach (self::$uopzSetFunctionStaticVariables as $function => $values) {
            $this->resetFunctionStaticVariables($function);
        }

        foreach (self::$uopzAddedFunctions as $function => $k) {
            $this->removeFunction($function);
        }
    }
}
