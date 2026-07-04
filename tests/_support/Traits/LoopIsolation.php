<?php

namespace lucatume\WPBrowser\Tests\Traits;

use Closure;

trait LoopIsolation
{
    protected function assertInIsolation(Closure $runAssertions, ?string $cwd = null): mixed
    {
        return Fork::executeClosure(static function () use ($runAssertions, $cwd) {
            if ($cwd !== null && $cwd !== '') {
                chdir($cwd);
            }
            return $runAssertions();
        });
    }
}
