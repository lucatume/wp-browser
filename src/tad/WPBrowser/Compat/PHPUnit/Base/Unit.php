<?php
/**
 * An extension of Codeception Unit TestCase to cover all methods required by the test cases to work.
 * This specific file will be loaded when the loaded version of PHPUnit is < 8.0.
 *
 * @package tad\WPBrowser\Compat\Codeception
 * @since TBD
 */

namespace tad\WPBrowser\Compat\Codeception;

/**
 * Class Unit
 * @package tad\WPBrowser\Compat\Codeception
 */
class Unit extends \Codeception\Test\Unit
{
    protected function assertPreConditions()
    {
        if (method_exists(get_called_class(), '_assertPreConditions')) {
            static::_assertPreConditions();
        }
    }

    protected function assertPostConditions()
    {
        if (method_exists(get_called_class(), '_assertPostConditions')) {
            static::_assertPostConditions();
        }
    }
}