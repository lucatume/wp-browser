<?php

use Codeception\Stub\Expected;

class UnitExtensionTest extends \Codeception\TestCase\WPTestCase
{
    /**
     * It should allow accessing the tester property
     *
     * @test
     */
    public function should_allow_accessing_the_tester_property()
    {
        $this->assertInstanceOf(WploaderTester::class, $this->tester);
    }

    /**
     * It should allow using Codeception stubs
     *
     * @test
     */
    public function should_allow_using_codeception_stubs()
    {
        $stub = $this->make(WP_User::class, ['get_site_id' => 23]);

        $this->assertEquals(23, $stub->get_site_id());
    }

    /**
     * It should allow using codeception dummys
     *
     * @test
     */
    public function should_allow_using_codeception_dummys()
    {
        $stub = $this->makeEmpty(WP_User::class);

        $this->assertNull($stub->get_site_id());
    }

    /**
     * It should allow using codeception partial mocks
     *
     * @test
     */
    public function should_allow_using_codeception_partial_mocks()
    {
        $realUser = new WP_User;
        $stub = $this->makeEmptyExcept(WP_User::class, 'get_site_id');

        $this->assertEquals($realUser->get_site_id(), $stub->get_site_id());
    }

    /**
     * It should allow using Codeception stubs with constructors
     *
     * @test
     */
    public function should_allow_using_codeception_stubs_with_constructors()
    {
        $realUser = static::factory()->user->create_and_get();
        $stub = $this->construct(
            WP_User::class,
            ['id' => $realUser->ID, 'name' => $realUser->display_name],
            ['to_array' => 23]
        );

        $this->assertEquals(23, $stub->to_array());
    }

    /**
     * It should allow using Codeception stubs with constructor and empty method
     *
     * @test
     */
    public function should_allow_using_codeception_stubs_with_constructor_and_empty_method()
    {
        $realUser = static::factory()->user->create_and_get();
        $stub = $this->constructEmpty(
            WP_User::class,
            ['id' => $realUser->ID, 'name' => $realUser->display_name]
        );

        $this->assertNull($stub->to_array());
    }
}
