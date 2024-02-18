<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Class DynamicPropertyTest.
 *
 * @since TBD
 *
 * @package wploadersuite;
 */
class DynamicPropertyTest extends WPTestCase
{
    /**
     * It should allow setting and getting a dynamic property on the test case
     *
     * @test
     */
    public function should_allow_setting_and_getting_a_dynamic_property_on_the_test_case(): void
    {
        $this->assertFalse(isset($this->testDynamicProperty));
        $this->assertNull($this->testDynamicProperty);

        $this->testDynamicProperty = 23;

        $this->assertTrue(isset($this->testDynamicProperty));
        $this->assertEquals(23,$this->testDynamicProperty);

        $this->testDynamicProperty = 89;

        $this->assertTrue(isset($this->testDynamicProperty));
        $this->assertEquals(89,$this->testDynamicProperty);

        unset($this->testDynamicProperty);

        $this->assertFalse(isset($this->testDynamicProperty));
        $this->assertNull($this->testDynamicProperty);
    }
}
