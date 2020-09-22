<?php
/**
 * Generates dates in WordPress database format.
 *
 * @package tad\WPBrowser\Generators
 */

namespace tad\WPBrowser\Generators;

/**
 * Class Date
 *
 * @package tad\WPBrowser\Generators
 */
class Date
{
    /**
     * The date format used in WordPress databases.
     */
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var int An injectable time value, used in tests.
     */
    protected static $time;

    /**
     * Returns the current time in WordPress specific format.
     *
     * @return string|false The current time in WordPress specific format like "2014-06-16 08:27:21" or
     *                      `false` on failure.
     */
    public static function now()
    {
        return date(self::DATE_FORMAT, self::_time());
    }

    /**
     * Proxy to `time()` to allow mocking.
     *
     * @return int The current time.
     */
    public static function _time()
    {
        $time = self::$time ? self::$time : time();

        return $time;
    }

    /**
     * Returns the 0 time string in WordPress specific format.
     *
     * @return string The "0000-00-00 00:00:00" string.
     */
    public static function zero()
    {
        return '0000-00-00 00:00:00';
    }

    /**
     * The current date in GMT time.
     *
     * @return false|string The formatted date or `false` on failure.
     */
    public static function gmtNow()
    {
        return gmdate(self::DATE_FORMAT, self::_time());
    }

    /**
     * Injects the value of the "now" time for testing purposes.
     *
     * @param int $now The mock "now" timestamp.
     *
     * @return void
     */
    public static function _injectNow($now)
    {
        self::$time = $now;
    }

    /**
     * Returns a WordPress database format date from an english format string.
     *
     * E.g.: `Date::fromString('February 4th, 2015');`
     *
     * @param string $strtotime An english format string, same type used in `strtotime` functions.
     *
     * @return string A date in WordPress database format, 'Y-m-d H:i:s'
     */
    public static function fromString($strtotime)
    {
        $timestamp = strtotime($strtotime);

        if ($timestamp === false) {
            throw new \InvalidArgumentException('Invalid time: ' . $strtotime);
        }

        return date(self::DATE_FORMAT);
    }
}
