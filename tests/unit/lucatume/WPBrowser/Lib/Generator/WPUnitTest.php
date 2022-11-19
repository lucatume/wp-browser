<?php

namespace lucatume\WPBrowser\Lib\Generator;

use lucatume\WPBrowser\TestCase\WPTestCase;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class WPUnitTest extends \Codeception\Test\Unit
{

    use SnapshotAssertions;

    /**
     * It should scaffold the test case
     *
     * @test
     */
    public function should_scaffold_the_test_case()
    {
        $settings = ['namespace' => 'Acme'];
        $name = 'SomeClass';
        $generator = new WPUnit($settings, $name, WPTestCase::class);

        $code = $generator->produce();

        $this->assertMatchesCodeSnapshot($code, 'php');
    }

    /**
     * It should correctly add the tester property if actor is set in the settings
     *
     * @test
     */
    public function should_correctly_add_the_tester_property_if_actor_is_set_in_the_settings()
    {
        $settings = ['namespace' => 'Acme', 'actor' => 'Fixer'];
        $name = 'SomeClass';
        $generator = new WPUnit($settings, $name, WPTestCase::class);

        $code = $generator->produce();

        $this->assertMatchesCodeSnapshot($code, 'php');
    }
}
