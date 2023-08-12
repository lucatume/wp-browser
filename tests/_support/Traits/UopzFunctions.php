<?php

namespace lucatume\WPBrowser\Tests\Traits;

use Exception;
use Generator;
use RuntimeException;

trait UopzFunctions
{
    protected static array $uopzSetFunctionReturns = [];
    protected static array $uopzSetStaticMethodReturns = [];
    protected static array $uopzRedefinedConstants = [];
    protected static array $uopzRedefinedClassConstants = [];
    protected static array $uopzSetMocks = [];

    protected function replacingWithUopz(array $what, callable $do): void
    {
        if (!function_exists('uopz_set_return')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        $replaced = [];
        foreach ($what as $key => $value) {
            if (function_exists($key)) {
                uopz_set_return($key, $value, is_callable($value));
                $replaced[$key] = 'func';
                continue;
            } elseif (strpos($key, '::')) {
                list($class, $method) = explode('::', $key, 2);
                uopz_set_return($class, $method, $value, is_callable($value));
                $replaced[$key] = 'static-method';
                continue;
            }

            throw new RuntimeException("{$key} is neither a function nor a static method.");
        }

        try {
            $do();
        } catch (Exception $e) {
            foreach ($replaced as $key => $type) {
                if ($type === 'func') {
                    uopz_unset_return($key);
                } elseif ($type === 'static-method') {
                    list($class, $method) = explode('::', $key, 2);
                    uopz_unset_return($class, $method);
                }
            }
            throw $e;
        }
    }

    protected function uopzSetFunctionReturn(string $function, $return, bool $execute = false): void
    {
        if (!function_exists('uopz_set_return')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        uopz_set_return($function, $return, $execute);
        self::$uopzSetFunctionReturns[] = $function;
    }

    protected function uopzSetStaticMethodReturn(
        string $class,
        string $method,
        mixed $return,
        bool $execute = false
    ): void {
        if (!function_exists('uopz_set_return')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        uopz_set_return($class, $method, $return, $execute);
        self::$uopzSetStaticMethodReturns[] = $class . '::' . $method;
    }

    protected function uopzRedefineConstant(string $constant, string|int|bool|null $value): void
    {
        if (!function_exists('uopz_set_return')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        uopz_redefine($constant, $value);
        $wasDefined = defined($constant);
        $previousValue = $wasDefined ? constant($constant) : null;
        self::$uopzRedefinedConstants[$constant] = [$wasDefined, $previousValue];
    }

    protected function uopzRedefinedClassConstant(string $class, string $constant, string|int|bool|null $value): void
    {
        if (!function_exists('uopz_set_return')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        uopz_redefine($class, $constant, $value);
        $wasDefined = defined($class . '::' . $constant);
        $previousValue = $wasDefined ? constant($class . '::' . $constant) : null;
        self::$uopzRedefinedClassConstants[$class . '::' . $constant] = [$wasDefined, $previousValue];
    }

    /**
     * @after
     */
    public function uopzTearDown(): void
    {
        foreach (self::$uopzSetFunctionReturns as $function) {
            uopz_unset_return($function);
        }
        self::$uopzSetFunctionReturns = [];

        foreach (self::$uopzSetStaticMethodReturns as $classAndMethod) {
            [$class, $function] = explode('::', $classAndMethod, 2);
            uopz_unset_return($class, $function);
        }
        self::$uopzSetStaticMethodReturns = [];

        foreach (self::$uopzRedefinedConstants as $constant => [$wasDefined, $previousValue]) {
            uopz_undefine($constant);
            if ($wasDefined) {
                uopz_redefine($constant, $previousValue);
            }
        }
        self::$uopzRedefinedConstants = [];

        foreach (self::$uopzRedefinedClassConstants as $classAndConstant => [$wasDefined, $previousValue]) {
            [$class, $constant] = explode($classAndConstant, '::', 2);
            uopz_undefine($class, $constant);
            if ($wasDefined) {
                uopz_redefine($class, $constant, $previousValue);
            }
        }
        self::$uopzRedefinedClassConstants = [];

        foreach (self::$uopzSetMocks as $class) {
            uopz_unset_mock($class);
        }
        self::$uopzSetMocks = [];
    }

    protected function uopzUndefineConstant(string $constant): void
    {
        if (!function_exists('uopz_set_return')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        if (!defined($constant)) {
            // The constant was not defined, let's make sure to reset during tear down.
            self::$uopzRedefinedConstants[$constant] = [false, null];
            return;
        }

        self::$uopzRedefinedConstants[$constant] = [true, constant($constant)];

        uopz_undefine($constant);
    }

    protected function uopzSetMock(string $class, string|object $mock): void
    {
        if (!function_exists('uopz_set_return')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        self::$uopzSetMocks[] = $class;
        if ($mock instanceof Generator) {
            $mock = $mock->current();
        }
        uopz_set_mock($class, $mock);
    }

    protected function uopzUnsetMock(string $class): void
    {
        if (!function_exists('uopz_unset_mock')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        $index = array_search($class, self::$uopzSetMocks, true);

        if ($index === false) {
            return;
        }

        unset(self::$uopzSetMocks[$index]);
        uopz_unset_mock($class);
    }
}
