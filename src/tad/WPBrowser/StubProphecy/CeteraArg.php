<?php
/**
 * An argument that will match the rest of the method arguments.
 *
 * @package tad\WPBrowser\StubProphecy
 */

namespace tad\WPBrowser\StubProphecy;

/**
 * Class CeteraArg
 *
 * @package tad\WPBrowser\StubProphecy
 */
class CeteraArg implements ArgInterface
{

    /**
     * {@inheritDoc}
     */
    public function verify($actual)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getFailureMessage()
    {
        return 'Expected some arguments, got none.';
    }

    /**
     * {@inheritDoc}
     */
    public function stopVerification()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function appliesToFollowing()
    {
        return true;
    }
}
