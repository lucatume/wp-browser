<?php
/**
 * An adapter to deal with different versions of PHPUnit assertions.
 *
 * @package tad\WPBrowser\Adapters
 */

namespace tad\WPBrowser\Adapters\PHPUnit\Framework;

use PHPUnit\Framework\Assert as PHPUnitAssert;

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
        if (method_exists(PHPUnitAssert::class, 'assertStringNotContainsString')) {
            PHPUnitAssert::assertStringNotContainsString($needle, $haystack);
        } else {
            PHPUnitAssert::assertNotContains($needle, $haystack);
        }
    }

    /**
     * Adapter and proxy for the `assertFileDoesNotExist` method.
     *
     * @param string $file The file to check.
     */
    public static function assertFileDoesNotExist($file)
    {
        if (method_exists(PHPUnitAssert::class, 'assertFileDoesNotExist')) {
            PHPUnitAssert::assertFileDoesNotExist($file);
        } else {
            PHPUnitAssert::assertFileNotExists($file);
        }
    }

    /**
     * Adapter and proxy for the `assertMatchesRegularExpression` method.
     *
     * @param string $pattern The regex pattern to match.
     * @param string $string  The string to check.
     * @param string $message The failure message.
     */
    public static function assertMatchesRegularExpression($pattern, $string, $message = '')
    {
        if (method_exists(PHPUnitAssert::class, 'assertMatchesRegularExpression')) {
            PHPUnitAssert::assertMatchesRegularExpression($pattern, $string, $message);
        } else {
            PHPUnitAssert::assertRegExp($pattern, $string, $message);
        }
    }

    /**
     * Adapter and proxy for the `assertDoesNotMatchRegularExpression` method.
     *
     * @param string $pattern The regex pattern to not match.
     * @param string $string  The string to check.
     * @param string $message The failure message.
     */
    public static function assertDoesNotMatchRegularExpression($pattern, $string, $message = '')
    {
        if (method_exists(PHPUnitAssert::class, 'assertDoesNotMatchRegularExpression')) {
            PHPUnitAssert::assertDoesNotMatchRegularExpression($pattern, $string, $message);
        } else {
            PHPUnitAssert::assertNotRegExp($pattern, $string, $message);
        }
    }
}
