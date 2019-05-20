<?php

namespace tad\WPBrowser\Generators;

/**
 * Generates dates in WordPress database format.
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
     * @return string The current time in WordPress specific format like "2014-06-16 08:27:21"
     */
    public static function now()
    {
        return date(self::DATE_FORMAT, self::_time());
    }

    /**
     * Returns the 0 time string in WordPress specific format.
     * @return string The "0000-00-00 00:00:00" string.
     */
    public static function zero()
    {
        return '0000-00-00 00:00:00';
    }

    public static function gmtNow()
    {
        return gmdate(self::DATE_FORMAT, self::_time());
    }

    public static function _injectNow($now)
    {
        self::$time = $now;
    }

    /**
     * @return int
     */
    public static function _time()
    {
        $time = self::$time ? self::$time : time();

        return $time;
    }

    /**
     * Returns a WordPress database format date from an english format string.
     *
     * E.g.: `Date::fromString('February 4th, 2015');`
     *
     * @param string $string An english format string, same type used in `strtotime` functions.
     * @return string A date in WordPress database format, 'Y-m-d H:i:s'
     *
     */
    public static function fromString($string)
    {
        return date(self::DATE_FORMAT, strtotime($string));
    }
}
