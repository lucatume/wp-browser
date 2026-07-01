<?php

namespace lucatume\WPBrowser\Tests\Traits;

use Closure;

trait PhaseTimer
{
    /**
     * @var array<string, float>
     */
    private $phaseTimings = [];

    /**
     * Runs a closure, records its wall time, and emits it via codecept_debug.
     *
     * @template T
     * @param string $label
     * @param Closure(): T $fn
     * @return T
     */
    protected function phase(string $label, Closure $fn)
    {
        $start = hrtime(true);
        try {
            return $fn();
        } finally {
            $elapsed = (hrtime(true) - $start) / 1e9;
            $this->phaseTimings[$label] = ($this->phaseTimings[$label] ?? 0) + $elapsed;
            codecept_debug(sprintf('[PHASE] %-50s %7.3fs', $label, $elapsed));
        }
    }

    /**
     * @after
     */
    protected function dumpPhaseTimings(): void
    {
        if (empty($this->phaseTimings)) {
            return;
        }

        codecept_debug('--- PHASE TOTALS ---');
        $total = 0.0;
        foreach ($this->phaseTimings as $label => $seconds) {
            $total += $seconds;
            codecept_debug(sprintf('[TOTAL] %-50s %7.3fs', $label, $seconds));
        }
        codecept_debug(sprintf('[TOTAL] %-50s %7.3fs', 'sum', $total));
        $this->phaseTimings = [];
    }
}
