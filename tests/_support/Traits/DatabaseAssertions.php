<?php

namespace lucatume\WPBrowser\Tests\Traits;

use mysqli;
use PHPUnit\Framework\Assert;
use Throwable;

trait DatabaseAssertions
{
    private static function assertDatabaseExists(
        ?string $host = null,
        ?string $user = null,
        ?string $password = null,
        ?string $database = null
    ): void {
        $data = json_encode(array_combine([
            'host',
            'user',
            'password',
            'database',
        ],
            func_get_args()
        ));
        try {
            $mysqli = mysqli_connect($host, $user, $password, $database);
            Assert::assertInstanceOF(mysqli::class, $mysqli, "Failed asserting the database exists: $data.");
            $mysqli->close();
        } catch (Throwable $t) {
            Assert::fail("Failed asserting the database exists: $data.");
        }
    }
}
