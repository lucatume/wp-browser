<?php

namespace lucatume\WPBrowser\Command\ParallelRun;

/**
 * @internal
 */
final class WorkerResourceEnv
{
    public const ENV_NEEDS_SERVER       = 'WPBROWSER_PARALLEL_WORKER_NEEDS_SERVER';
    public const ENV_NEEDS_CHROMEDRIVER = 'WPBROWSER_PARALLEL_WORKER_NEEDS_CHROMEDRIVER';

    /**
     * Returns true when the producer-set env var exists and equals exactly the string "0".
     * Any other value (unset, "1", "true", etc.) returns false — fail-safe by design.
     */
    public static function isDisabled(string $envVar): bool
    {
        $value = $_SERVER[$envVar] ?? $_ENV[$envVar] ?? getenv($envVar);
        return $value !== false && $value !== null && (string)$value === '0';
    }

    /**
     * Build per-worker env var map from shard file assignments.
     *
     * Returns only workers whose files are known. The ParallelRun caller is
     * responsible for supplying fail-safe defaults (both flags = "1") for any
     * worker not present in the result, e.g. when the shard planner delegates
     * file distribution to Codeception's own --shard mode.
     *
     * @param array<int, array{files: string[], weight: float}> $shardAssignments 1-indexed
     * @return array<int, array<string,string>>
     */
    public static function build(array $shardAssignments): array
    {
        $planner = new ShardPlanner();
        $out = [];
        foreach ($shardAssignments as $i => $shard) {
            $need = $planner->needsResources($shard['files'] ?? []);
            $out[$i] = [
                self::ENV_NEEDS_SERVER       => $need['server'] ? '1' : '0',
                self::ENV_NEEDS_CHROMEDRIVER => $need['chromedriver'] ? '1' : '0',
            ];
        }
        return $out;
    }
}
