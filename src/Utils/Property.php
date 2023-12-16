<?php
declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

use InvalidArgumentException;
use ReflectionClass;
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

        if (!class_exists($class)) {
            throw new InvalidArgumentException(
                sprintf('Class "%s" does not exists', $class)
            );
        }

        do {
            $reflectionClass = new ReflectionClass($class);
            if ($reflectionClass->hasProperty($property)) {
                $reflectionProperty = $reflectionClass->getProperty($property);
                $reflectionProperty->setAccessible(true);

                return $reflectionProperty->getValue($reflectionObject);
            }
            $class = get_parent_class($class);
        } while ($class);

        throw new InvalidArgumentException(
            sprintf('Property "%s" does not exists in class "%s" or its parents', $property, $originalClass)
        );
    }

    /**
     * Sets private and protected properties for an object of a class.
     *
     * @param object|null $object $object The object to set the properties of, `null` if using a class.
     * @param string $class The object class to set the properties for.
     * @param array<string,mixed> $props A map of the names and values of the properties to set.
     *
     * @return object The updated object.
     *
     * @throws ReflectionException If there's an issue reflecting on the object or its properties.
     * @throws InvalidArgumentException If the class does not exists or the constructor parameters are missing.
     */
    public static function setPropertiesForClass(?object $object, string $class, array $props): object
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException(
                sprintf('Class "%s" does not exists', $class)
            );
        }

        $reflectedEntity = new ReflectionClass($class);

        if (!$object) {
            $constructorParameters = [];
            $constructor = $reflectedEntity->getConstructor();
            if (null !== $constructor) {
                foreach ($constructor->getParameters() as $parameter) {
                    if ($parameter->isOptional()) {
                        $constructorParameters[] = $parameter->getDefaultValue();
                    } elseif (array_key_exists($parameter->getName(), $props)) {
                        $constructorParameters[] = $props[$parameter->getName()];
                    } else {
                        throw new InvalidArgumentException(
                            'Constructor parameter "' . $parameter->getName() . '" missing'
                        );
                    }
                }
            }

            $object = $reflectedEntity->newInstance(...$constructorParameters);
        }

        foreach ($reflectedEntity->getProperties() as $property) {
            if (isset($props[$property->name])) {
                $property->setAccessible(true);
                $property->setValue($object, $props[$property->name]);
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
     * @param object|string $object $object The object to set the properties of.
     * @param array<string,mixed> $props A map of the names and values of the properties to set.
     *
     * @throws ReflectionException
     */
    public static function setPrivateProperties(object|string $object, array $props): void
    {
        if (is_string($object)) {
            $class = $object;
            $object = null;
        } else {
            $class = $object::class;
        }

        do {
            $object = self::setPropertiesForClass($object, $class, $props);
            $class = get_parent_class($class);
        } while ($class);
    }
}
