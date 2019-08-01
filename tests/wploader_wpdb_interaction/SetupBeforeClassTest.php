<?php

class SetupBeforeClassTest extends \Codeception\TestCase\WPTestCase
{
    public static function _setUpBeforeClass()
    {
        return parent::_setUpBeforeClass();
    }

    /**
     * Test true is true
     */
    public function test_true_is_true()
    {
        $this->assertTrue(true);
    }
}
