<?php

declare(strict_types=1);

/**
 * Provides methods to interact and build dates.
 *
 * @package lucatume\WPBrowser\Utils;
 */

namespace lucatume\WPBrowser\Utils;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use InvalidArgumentException;

/**
 * Class Dates.
 *
 * @package lucatume\WPBrowser\Utils;
 */
class Dates
{
    /**
     * Builds an immutable date object starting from different input values.
     *
     * @param DateTimeInterface|int|string $date The date input to build from.
     *
     * @return DateTimeImmutable The built date.
     *
     * @throws Exception If there's an issue building the date.
     * @throws InvalidArgumentException If the date is not valid.
     */
    public static function immutable(DateTimeInterface|int|string $date): DateTimeImmutable
    {
        if ($date instanceof DateTimeImmutable) {
            return $date;
        }

        if (is_numeric($date) || is_string($date)) {
            $input = '@' . (is_numeric($date) ? $date : strtotime($date));
            return (new DateTimeImmutable($input))->setTimezone(new DateTimezone(date_default_timezone_get()));
        }

        // The `DateTimeInterface` cannot be implemented by user-land classes, so we can safely assume it's a DateTime.
        return DateTimeImmutable::createFromMutable($date);
    }
}
