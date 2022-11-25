<?php

namespace lucatume\WPBrowser\Utils;

use Codeception\Test\Unit;

class PasswordTest extends Unit
{

    public function testSaltDataProvider(): array
    {
        return [
            'length 0' => [0],
            'length 23' => [23],
            'length 32' => [32],
            'length 64' => [64],
            'length 128' => [128],
        ];
    }

    /**
     * @dataProvider testSaltDataProvider
     */
    public function test_salt(int $len): void
    {
        $salt1 = Random::salt($len);
        $salt2 = Random::salt($len);
        $this->assertEquals(strlen(Random::salt($len)), $len);
        $this->assertEquals(strlen(Random::salt($len)), $len);
        if ($len > 0) {
            $this->assertNotEquals($salt1, $salt2);
        }
    }
}
