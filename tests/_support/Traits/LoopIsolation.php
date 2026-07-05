<?php

namespace lucatume\WPBrowser\Tests\Traits;

use Closure;

trait LoopIsolation
{
    /**
     * @return mixed
     */
    protected function assertInIsolation(Closure $runAssertions, ?string $cwd = null)
    {
        return Fork::executeClosure(static function () use ($runAssertions, $cwd) {
            if ($cwd !== null && $cwd !== '' && !chdir($cwd)) {
                throw new \RuntimeException("Could not change directory to $cwd");
            }
            return $runAssertions();
        });
    }
}
