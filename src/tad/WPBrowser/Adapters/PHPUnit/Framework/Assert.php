<?php
/**
 * An adapter to deal with different versions of PHPUnit assertions.
 *
 * @package tad\WPBrowser\Adapters
 */

namespace tad\WPBrowser\Adapters\PHPUnit\Framework;

/**
 * Class Assert
 *
 * @package tad\WPBrowser\Adapters\PHPUnit\Framework
 */
class Assert
{
    /**
     * Adapter and proxy for the `assertStringNotContainsString` method.
     *
     * @param string $needle   The search string.
     * @param string $haystack The search target.
     */
    public static function assertStringNotContainsString($needle, $haystack)
    {
        if (method_exists(Assert::class, 'assertStringNotContainsString')) {
            \PHPUnit\Framework\Assert::assertStringNotContainsString($needle, $haystack);
        } else {
            \PHPUnit\Framework\Assert::assertNotContains($needle, $haystack);
        }
    }
}
