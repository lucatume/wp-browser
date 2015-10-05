<?php

namespace tad\WPBrowser\Utils;

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
     * Returns the current time in WordPress specific format.
     *
     * @return string The current time in WordPress specific format like "2014-06-16 08:27:21"
     */
    public static function now()
    {
        return date(self::DATE_FORMAT);
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
        return gmdate(self::DATE_FORMAT);
    }
} 