<?php
/**
 * The interface implemented by argument expectations.
 *
 * @package tad\WPBrowser\StubProphecy
 */

namespace tad\WPBrowser\StubProphecy;

/**
 * Interface ArgInterface
 *
 * @package tad\WPBrowser\StubProphecy
 */
interface ArgInterface
{

    /**
     * Verifies the actual argument matches the expectation.
     *
     * @param mixed $actual The actual argument value.
     *
     * @return bool Whether the actual argument matches the expectation or not.
     */
    public function verify($actual);

    /**
     * Returns the failure message related to the expectation failure.
     *
     * @return string The failure message related to the expectation failure.
     */
    public function getFailureMessage();

    /**
     * Returns whether argument verification should be stopped at this argument or not.
     *
     * @return bool Whether argument verification should be stopped at this argument or not.
     */
    public function stopVerification();

    /**
     * Returns whether this argument expectation will apply to the next argument too or not.
     *
     * @return bool Whether this argument expectation will apply to the next argument too or not.
     */
    public function appliesToFollowing();
}
