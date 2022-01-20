<?php

namespace lucatume\WPBrowser\Tests\Traits;

trait WithUopz
{
    /**
     * Replaces a set of functions or static methods using the uopz extension, calls a function and restores the
     * original return values.
     *
     * @param array<string,mixed> $what A map relating the name of the function or static method to their replacements.
     *                                  Static methods should have key `class::method`.
     * @param callable            $do   The callback that will be called while replacements are in place.
     *
     * @return void
     * @throws \RuntimeException If the uopz extension is not loaded or a defined replacement is neither a function nor
     *                           a static method.
     *
     */
    function replacingWithUopz(array $what, callable $do)
    {
        if (! function_exists('uopz_set_return')) {
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
}
