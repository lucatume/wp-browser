<?php
/**
 * Provides methods to ensure test case compatibility with PHPUnit request time values.
 *
 * @package tad\WPBrowser\Traits
 */

namespace tad\WPBrowser\Traits;

/**
 * Trait WithRequestTime
 *
 * @package tad\WPBrowser\Traits
 */
trait WithRequestTime
{

    /**
     * The request time, an integer UNIX timestamp.
     *
     * @var int
     */
    protected $requestTime;

    /**
     * The request time in float format.
     *
     * @var float
     */
    protected $requestTimeFloat;

    /**
     * Sets up the trait request time in integer and float format.
     *
     * This method should be called in the test case set up method, to then use the values in tear down
     * to ensure the `$_SERVER['REQUEST_TIME']` and `$_SERVER['REQUEST_TIME_FLOAT']` are set.
     *
     * @link  https://github.com/sebastianbergmann/phpunit/issues/3026#issuecomment-368842252
     */
    protected function requestTimeSetUp()
    {
        $this->requestTime      = time();
        $this->requestTimeFloat = microtime(true);
    }

    /**
     * This method ensures the `$_SERVER['REQUEST_TIME']` and `$_SERVER['REQUEST_TIME_FLOAT`]`
     * variables are set.
     *
     * This method should run in the test case tear down method to ensure the two variables PHPUnit
     * will look for to time the tests are set.
     * The problem of those variables not being set could be solved by setting the test case
     * `$backupGlobals = true`, but that would come with its set of issues in the context of WordPress
     * integration tests.
     *
     * @link https://github.com/sebastianbergmann/phpunit/issues/3026#issuecomment-368842252
     *
     * @throws \RuntimeException If the `$requestTime` or `$requestTimeFloat` property
     *                           are not set.
     */
    protected function requestTimeTearDown()
    {
        if (!isset($this->requestTime, $this->requestTimeFloat)) {
            throw new \RuntimeException(
                'The `$requestTime` or `$requestTimeFloat` properties are not set;' .
                ' did you call the requestTimeSetUp method in the test case setUp method?'
            );
        }

        $_SERVER['REQUEST_TIME']       = $this->requestTime;
        $_SERVER['REQUEST_TIME_FLOAT'] = $this->requestTimeFloat;
    }
}
