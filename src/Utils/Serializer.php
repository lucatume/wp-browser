<?php
declare(strict_types=1);

/**
 * Provides methods to serialize and unserialize data.
 *
 * @package lucatume\WPBrowser\Utils;
 */

namespace lucatume\WPBrowser\Utils;

use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use Throwable;

/**
 * Class Serializer.
 *
 * @package lucatume\WPBrowser\Utils;
 */
class Serializer
{
    public static function maybeUnserialize(mixed $value): mixed
    {
        if (!(is_string($value) && self::isSerialized($value))) {
            return $value;
        }

        return match (substr($value, 0, 4)) {
            'N;' => null,
            'b:1;' => true,
            'b:0;' => false,
            default => @unserialize($value, ['allowed_classes' => true])
        };
    }

    public static function isSerialized(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return match (substr($value, 0, 2)) {
            'N;', 'b:', 'i:', 'd:', 's:', 'a:', 'O:', 'C:' => true,
            default => false
        };
    }

    public static function maybeSerialize(mixed $value): mixed
    {
        return is_array($value) || is_object($value) || $value === null ? serialize($value) : $value;
    }

    public static function makeThrowableSerializable(Throwable $throwable): Throwable
    {
        $trace = Property::readPrivate($throwable, 'trace');

        if (!is_array($trace)) {
            return $throwable;
        }

        foreach ($trace as &$traceEntry) {
            unset($traceEntry['args']);
        }

        Property::setPrivateProperties($throwable, ['trace' => $trace]);

        if ($throwable instanceof ExpectationFailedException) {
            // @see https://github.com/sebastianbergmann/comparator/pull/47
            $comparisonFailure = $throwable->getComparisonFailure();
            if ($comparisonFailure instanceof ComparisonFailure) {
                self::makeThrowableSerializable($comparisonFailure);
            }
        }

        $previous = $throwable->getPrevious();
        if ($previous instanceof Throwable) {
            self::makeThrowableSerializable($previous);
        }

        return $throwable;
    }
}
