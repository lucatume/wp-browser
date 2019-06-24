<?php
/**
 * An extension of the PHPUnit TestCase wrapper provided by the `codeception/phpunit-wrapper` package to cover all
 * methods required by the test cases to work.
 * This specific file will be loaded when the loaded version of PHPUnit is < 8.0.
 *
 * @package tad\WPBrowser\Compat\PHPUnit
 */

namespace tad\WPBrowser\Compat\PHPUnit;

use Codeception\Test\Unit;
use Codeception\TestCase\WPTestCase;

/**
 * Class Testcase
 *
 * @package tad\WPBrowser\Compat\PHPUnit
 */
class Testcase extends \Codeception\PHPUnit\TestCase
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
