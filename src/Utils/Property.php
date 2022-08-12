<?php
declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

use ReflectionException;

class Property
{

    /**
     * @throws ReflectionException
     */
    public static function readPrivate(object|string $object, string $property): mixed
    {
        $originalClass = is_object($object) ? $object::class : $object;
        $reflectionObject = is_object($object) ? $object : null;
        $class = $originalClass;

        do {
            $reflectionClass = new \ReflectionClass($class);
            if ($reflectionClass->hasProperty($property)) {
                $reflectionProperty = $reflectionClass->getProperty($property);
                $reflectionProperty->setAccessible(true);

                return $reflectionProperty->getValue($reflectionObject);
            }
            $class = get_parent_class($class);
        } while ($class);

        throw new \InvalidArgumentException(
            sprintf('Property "%s" does not exists in class "%s" or its parents', $property, $originalClass)
        );
    }
}
