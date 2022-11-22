<?php

namespace lucatume\WPBrowser\Tests\Traits;

trait WithUopz
{
    private array $uopzSetFunctionReturns = [];
    private array $uopzSetStaticMethodReturns = [];
    private array $uopzRedefinedConstants = [];
    private array $uopzRedefinedClassConstants = [];

    /**
     * Replaces a set of functions or static methods using the uopz extension, calls a function and restores the
     * original return values.
     *
     * @param array<string,mixed> $what A map relating the name of the function or static method to their replacements.
     *                                  Static methods should have key `class::method`.
     * @param callable            $do   The callback that will be called while replacements are in place.
     *
     * @return void
     * @throws \RuntimeException|\Exception If the uopz extension is not loaded or a defined replacement is neither
     *                                      a function nor a static method.
     */
    function replacingWithUopz(array $what, callable $do)
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

            throw new \RuntimeException("{$key} is neither a function nor a static method.");
        }

        try {
            $do();
        } catch (\Exception $e) {
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

    private function uopzSetFunctionReturn(string $function, $return, bool $execute = false): void
    {
        if (!function_exists('uopz_set_return')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        uopz_set_return($function, $return, $execute);
        $this->uopzSetFunctionReturns[] = $function;
    }

    private function uopzSetStaticMethodReturn(
        string $class,
        string $method,
        mixed $return,
        bool $execute = false
    ): void {
        if (!function_exists('uopz_set_return')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        uopz_set_return($class, $method, $return, $execute);
        $this->uopzSetStaticMethodReturns[] = $class . '::' . $method;
    }

    private function uopzRedefinedConstant(string $constant, string|int|bool|null $value): void
    {
        if (!function_exists('uopz_set_return')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        uopz_redefine($constant, $value);
        $wasDefined = defined($constant);
        $previousValue = $wasDefined ? constant($constant) : null;
        $this->uopzRedefinedConstants[$constant] = [$wasDefined, $previousValue];
    }

    private function uopzRedefinedClassConstant(string $class, string $constant, string|int|bool|null $value): void
    {
        if (!function_exists('uopz_set_return')) {
            $this->markTestSkipped('This test requires the uopz extension');
        }

        uopz_redefine($class, $constant, $value);
        $wasDefined = defined($class . '::' . $constant);
        $previousValue = $wasDefined ? constant($class . '::' . $constant) : null;
        $this->uopzRedefinedClassConstants[$class . '::' . $constant] = [$wasDefined, $previousValue];;
    }

    /**
     * @after
     */
    public function uopzTearDown(): void
    {
        foreach ($this->uopzSetFunctionReturns as $function) {
            uopz_unset_return($function);
        }

        foreach ($this->uopzSetStaticMethodReturns as $classAndMethod) {
            [$class, $function] = explode('::', $classAndMethod, 2);
            uopz_unset_return($class, $function);
        }

        foreach ($this->uopzRedefinedConstants as $constant => [$wasDefined, $previousValue]) {
            uopz_undefine($constant);
            if ($wasDefined) {
                uopz_define($constant, $previousValue);
            }
        }

        foreach ($this->uopzRedefinedClassConstants as $classAndConstant => [$wasDefined, $previousValue]) {
            [$class, $constant] = explode($classAndConstant, '::', 2);
            uopz_undefine($class, $constant);
            if ($wasDefined) {
                uopz_define($class, $constant, $previousValue);
            }
        }
    }
}
