<?php

namespace lucatume\WPBrowser\Command\ParallelRun;

use RuntimeException;

/**
 * @internal
 */
final class PortAllocator
{
    public const MAX_PORT = 65535;

    /**
     * Returns $count free TCP ports starting the search at $preferredStart.
     *
     * Ports already taken or declared unavailable via $reserved are skipped.
     * A small TOCTOU window exists between this probe and the worker binding.
     *
     * @param array<int,true> $reserved
     * @return list<int>
     */
    public static function allocate(int $count, int $preferredStart, array $reserved = []): array
    {
        if ($count < 1) {
            return [];
        }
        $ports = [];
        $port  = max(1, $preferredStart);
        while (count($ports) < $count && $port <= self::MAX_PORT) {
            if (!isset($reserved[$port]) && self::isFree($port)) {
                $ports[]          = $port;
                $reserved[$port]  = true;
            }
            $port++;
        }
        if (count($ports) < $count) {
            throw new RuntimeException(sprintf(
                'Could not find %d free TCP ports starting at %d.',
                $count,
                $preferredStart
            ));
        }
        return $ports;
    }

    private static function isFree(int $port): bool
    {
        $sock = @stream_socket_server(
            "tcp://127.0.0.1:{$port}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
        );
        if ($sock === false) {
            return false;
        }
        fclose($sock);
        return true;
    }
}
