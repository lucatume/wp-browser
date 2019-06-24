<?php
/**
 * An extension of Codeception Unit TestCase to cover all methods required by the test cases to work.
 * This specific file will be loaded when the loaded version of PHPUnit is >= 8.0.
 *
 * @package tad\WPBrowser\Compat\Codeception
 */

namespace tad\WPBrowser\Compat\Codeception;

/**
 * Class Unit
 * @package tad\WPBrowser\Compat\Codeception
 */
class Unit extends \Codeception\Test\Unit
{
    public static function setUpBeforeClass() : void
    {
        if (method_exists(get_called_class(), '_setUpBeforeClass')) {
            static::_setUpBeforeClass();
        }
    }

    public static function tearDownAfterClass() : void
    {
        if (method_exists(get_called_class(), '_tearDownAfterClass')) {
            static::_tearDownAfterClass();
        }
    }
    protected function assertPreConditions(): void
    {
        if (method_exists(get_called_class(), '_assertPreConditions')) {
            static::_assertPreConditions();
        }
    }
    protected function assertPostConditions(): void
    {
        if (method_exists(get_called_class(), '_assertPostConditions')) {
            static::_assertPostConditions();
        }
    }

    protected function setUp(): void
    {
        if (method_exists($this, '_setUp')) {
            $this->_setUp();
        }
    }

    protected function tearDown(): void
    {
        if (method_exists($this, '_tearDown')) {
            $this->_tearDown();
        }
    }
}
