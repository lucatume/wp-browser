<?php
/**
 * Property access and manipulation functions.
 *
 * @package tad\WPBrowser
 */

namespace tad\WPBrowser;

/**
 * Sets private and protected properties for an object of a class.
 *
 * This is a polyfill of the `Codeception\Utils\ReflectionPropertyAccessor::setPropertiesForClass` method.
 * All credits to the Codeception team.
 *
 * @param object|mixed        $object The object to set the properties of.
 * @param string              $class  The object class to set the properties for.
 * @param array<string,mixed> $props  A map of the names and values of the properties to set.
 *
 * @return object The updated object.
 *
 * @throws \ReflectionException If there's an issue reflecting on the object or its properties.
 */
function setPropertiesForClass($object, $class, array $props)
{
    // @phpstan-ignore-next-line
    $reflectedEntity = new \ReflectionClass($class);

    if (! $object) {
        $constructorParameters = [];
        $constructor           = $reflectedEntity->getConstructor();
        if (null !== $constructor) {
            foreach ($constructor->getParameters() as $parameter) {
                if ($parameter->isOptional()) {
                    $constructorParameters[] = $parameter->getDefaultValue();
                } elseif (array_key_exists($parameter->getName(), $props)) {
                    $constructorParameters[] = $props[ $parameter->getName() ];
                } else {
                    throw new \InvalidArgumentException(
                        'Constructor parameter "' . $parameter->getName() . '" missing'
                    );
                }
            }
        }

        $object = $reflectedEntity->newInstance(...$constructorParameters);
    }

    foreach ($reflectedEntity->getProperties() as $property) {
        if (isset($props[ $property->name ])) {
            $property->setAccessible(true);
            $property->setValue($object, $props[ $property->name ]);
        }
    }

    return $object;
}

/**
 * Sets private and protected properties on an object.
 *
 * This is a polyfill of the `Codeception\Utils\ReflectionPropertyAccessor::setPrivateProperties` method.
 * All credits to the Codeception team.
 *
 * @param object|mixed        $object The object to set the properties of.
 * @param array<string,mixed> $props  A map of the names and values of the properties to set.
 *
 * @return void
 */
function setPrivateProperties($object, array $props)
{
    if (! is_object($object)) {
        throw new \InvalidArgumentException('Object is not an object.');
    }
    $class = get_class($object);
    do {
        $object = \tad\WPBrowser\setPropertiesForClass($object, $class, $props);
        $class  = get_parent_class($class);
    } while ($class);
}

/**
 * Reads the value of an object private property.
 *
 * This is a polyfill of the `Codeception\Utils\ReflectionPropertyAccessor::readPrivateProperty` method.
 * All credits to the Codeception team.
 *
 * @param object|mixed $object The object to read the property from.
 * @param string       $prop   The name of the property to get.
 *
 * @return mixed The value of the property.
 *
 * @throws \ReflectionException If there's an issue reflecting on the object or its property.
 */
function readPrivateProperty($object, $prop)
{
    if (! is_object($object)) {
        throw new \InvalidArgumentException(
            sprintf('Cannot get property "%s" of "%s", expecting object', $prop, gettype($object))
        );
    }
    $class = get_class($object);
    do {
        $reflectedEntity = new \ReflectionClass($class);
        if ($reflectedEntity->hasProperty($prop)) {
            $property = $reflectedEntity->getProperty($prop);
            $property->setAccessible(true);

            return $property->getValue($object);
        }
        $class = get_parent_class($class);
    } while ($class);
    throw new \InvalidArgumentException(
        sprintf('Property "%s" does not exists in class "%s" and its parents', $prop, get_class($object))
    );
}
