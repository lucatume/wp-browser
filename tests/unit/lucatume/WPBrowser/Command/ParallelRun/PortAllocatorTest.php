<?php

namespace Unit\lucatume\WPBrowser\Command\ParallelRun;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Command\ParallelRun\PortAllocator;

/**
 * @group fast
 */
class PortAllocatorTest extends Unit
{
    public function test_allocates_requested_count_of_free_ports(): void
    {
        $ports = PortAllocator::allocate(3, 55100);

        $this->assertCount(3, $ports);
        $this->assertSame(array_values($ports), array_unique($ports));
        foreach ($ports as $port) {
            $this->assertGreaterThanOrEqual(55100, $port);
            $this->assertLessThan(PortAllocator::MAX_PORT, $port);
        }
    }

    public function test_skips_a_bound_port(): void
    {
        $start = 55200;
        $holder = stream_socket_server("tcp://127.0.0.1:{$start}", $errno, $errstr);
        $this->assertNotFalse($holder, "precondition: port {$start} must be bindable");
        try {
            $ports = PortAllocator::allocate(2, $start);
            $this->assertNotContains($start, $ports);
            $this->assertCount(2, $ports);
        } finally {
            fclose($holder);
        }
    }

    public function test_reserved_ports_are_skipped_even_when_free(): void
    {
        $reserved = [55300 => true, 55301 => true];
        $ports = PortAllocator::allocate(2, 55300, $reserved);

        $this->assertNotContains(55300, $ports);
        $this->assertNotContains(55301, $ports);
    }

    public function test_returns_empty_for_zero_count(): void
    {
        $this->assertSame([], PortAllocator::allocate(0, 55400));
    }
}
