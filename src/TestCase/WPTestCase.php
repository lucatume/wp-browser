<?php
/** @noinspection MagicMethodsValidityInspection */

namespace lucatume\WPBrowser\TestCase;

use Codeception\Test\Unit;
use WP_UnitTest_Factory;
use WP_UnitTestCase;

/**
 * The base test case for "wp-unit" (integration) tests, brings the Core suite test case into Codeception system.
 */
class WPTestCase extends Unit
{
    /**
     * WordPress uses globals extensively: we cannot be strict about them changing.
     *
     * @var bool
     */
    protected bool $beStrictAboutChangesToGlobalState = false;

    /**
     * The `WP_UnitTestCase_Base` will handle this, do not let PHPUnit do it.
     *
     * @var bool
     */
    protected $backupGlobals = false;

    /**
     * Should a test case want to backup globals, then it should not touch these between tests.
     *
     * @var array<string>
     */
    protected $backupGlobalsExcludeList = ['wpdb', 'wp_query', 'wp'];

    /**
     * Static attributes might be used to cache values, reset them between tests.
     *
     * @var bool
     */
    protected $backupStaticAttributes = true;

    use CoreTestCaseMethodRedirection;

    private ?WP_UnitTestCase $coreTestCaseInstance = null;

    private function getCoreTestCaseInstance(): WP_UnitTestCase
    {
        if ($this->coreTestCaseInstance === null) {
            $this->coreTestCaseInstance = new class extends WP_UnitTestCase {
            };
        }

        return $this->coreTestCaseInstance;
    }

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
