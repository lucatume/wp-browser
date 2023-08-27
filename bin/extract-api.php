<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$class = $argv[1];

if (!class_exists($class)) {
    throw new \InvalidArgumentException("Class $class does not exist.");
}

$classReflection = new ReflectionClass($class);
$classMethods = $classReflection->getMethods(ReflectionMethod::IS_PUBLIC);
$apiClassMethods = array_filter($classMethods, function (ReflectionMethod $m): bool {
    return !str_starts_with($m->getName(), '_');
});
usort(
    $apiClassMethods,
    fn(ReflectionMethod $a, ReflectionMethod $b) => $a->getName() <=> $b->getName()
);

// From each ReflectionMethod create a string in the format `method_name( ...parameters) : return_type - description`
// and add it to the array of methods.
$methods = array_map(function (ReflectionMethod $m): string {
    $parameters = $m->getParameters();
    $parametersString = implode(
        ', ',
        array_map(
            fn(ReflectionParameter $p): string => sprintf(
                '%s%s%s%s$%s',
                $p->isPassedByReference() ? '&' : '',
                $p->hasType() ? $p->getType() . ' ' : '',
                $p->isVariadic() ? '...' : '',
                $p->isOptional() ? '[' : '',
                $p->getName() . ($p->isOptional() ? ']' : '')
            ),
            $parameters
        )
    );
    $returnType = $m->hasReturnType() ? (string)$m->getReturnType() : 'void';
    return sprintf(
        '* `%s(%s)` : `%s`',
        $m->getName(),
        $parametersString,
        $returnType
    );
}, $apiClassMethods);

array_map(fn(string $m) => print($m . PHP_EOL), $methods);
