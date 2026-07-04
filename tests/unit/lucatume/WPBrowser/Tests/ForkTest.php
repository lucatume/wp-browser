<?php

namespace Unit\lucatume\WPBrowser\Tests;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\Fork;

class ForkTest extends Unit
{
    public function testReturnsClosureResult(): void
    {
        $this->assertSame(['ok', 23], Fork::executeClosure(static fn(): array => ['ok', 23]));
    }

    public function testRethrowsChildThrowable(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('from the child');

        Fork::executeClosure(static function (): void {
            throw new \LogicException('from the child');
        });
    }

    public function testThrowsWhenChildDiesWithoutResult(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('without sending a result');

        Fork::executeClosure(static function (): void {
            posix_kill(getmypid(), 9);
        });
    }
}
