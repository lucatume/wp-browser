<?php

namespace lucatume\WPBrowser\Tests\Traits;

trait DatabaseAssertions
{
    private function assertDatabaseExists(
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
        ], func_get_args()
        ));
        try {
            $mysqli = mysqli_connect($host, $user, $password, $database);
            $this->assertInstanceOF(mysqli::class, $mysqli, "Failed asserting the database exists $data.");
            $mysqli->close();
        } catch (\Throwable $t) {
            $this->fail("Failed asserting the database exists $data.");
        }
    }
}
