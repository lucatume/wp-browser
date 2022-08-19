<?php
/** @noinspection MagicMethodsValidityInspection */

namespace lucatume\WPBrowser\TestCase;

use Codeception\Test\Unit;
use WP_UnitTest_Factory;

/**
 * The base test case for "wp-unit" (integration) tests, brings the Core suite test case into Codeception system.
 */
class WPTestCase extends Unit
{
    use CoreTestCaseMethodRedirection;

    public function __get(string $name)
    {
        if ($name === 'factory ') {
            $this->invokeProtectedApiMethod('factory');
        }
    }

    public function __isset(string $name)
    {
        return $name === 'factory';
    }

    protected static function factory(): WP_UnitTest_Factory
    {
        return self::invokeProtectedStaticApiMethod('factory');
    }

    protected function reset_post_types(): void
    {
        $this->invokeProtectedApiMethod('reset_post_types');
    }

    protected function reset_taxonomies(): void
    {
        $this->invokeProtectedApiMethod('reset_taxonomies');
    }

    protected function reset_post_statuses(): void
    {
        $this->invokeProtectedApiMethod('reset_post_statuses');
    }

    protected function reset__SERVER(): void
    {
        $this->invokeProtectedApiMethod('reset__SERVER');
    }

    protected function assert_post_conditions(): void
    {
        $this->invokeProtectedApiMethod('assert_post_conditions');
    }

    protected function update_post_modified(string $date): void
    {
        $this->invokeProtectedApiMethod('update_post_modified', [$date]);
    }
}
