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
    /**
     * @param mixed $value
     * @return mixed
     */
    public static function maybeUnserialize($value)
    {
        if (!(is_string($value) && self::isSerialized($value))) {
            return $value;
        }

        switch (substr($value, 0, 4)) {
            case 'N;':
                return null;
            case 'b:1;':
                return true;
            case 'b:0;':
                return false;
            default:
                return @unserialize($value, ['allowed_classes' => true]);
        }
    }

    /**
     * @param mixed $value
     */
    public static function isSerialized($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        switch (substr($value, 0, 2)) {
            case 'N;':
            case 'b:':
            case 'i:':
            case 'd:':
            case 's:':
            case 'a:':
            case 'O:':
            case 'C:':
                return true;
            default:
                return false;
        }
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function maybeSerialize($value)
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
