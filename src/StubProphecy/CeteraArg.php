<?php
/**
 * An argument that will match the rest of the method arguments.
 *
 * @package lucatume\WPBrowser\StubProphecy
 */

namespace lucatume\WPBrowser\StubProphecy;

/**
 * Class CeteraArg
 *
 * @package lucatume\WPBrowser\StubProphecy
 */
class CeteraArg implements ArgInterface
{

    /**
     * {@inheritDoc}
     */
    public function verify($actual): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getFailureMessage(): string
    {
        return 'Expected some arguments, got none.';
    }

    /**
     * {@inheritDoc}
     */
    public function stopVerification(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function appliesToFollowing(): bool
    {
        return true;
    }
}
