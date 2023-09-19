<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

$class = $argv[1];
$destFile = $argv[2];

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
                '%s%s%s%s$%s%s',
                $p->isOptional() ? '[' : '',
                $p->isPassedByReference() ? '&' : '',
                $p->hasType() ? $p->getType() . ' ' : '',
                $p->isVariadic() ? '...' : '',
                $p->getName(),
                $p->isOptional() ? ']' : ''
            ),
            $parameters
        )
    );
    $returnType = $m->hasReturnType() ? (string)$m->getReturnType() : 'void';
    $docBlock = $m->getDocComment();
    $textLines = array_filter(
        array_map(
            fn(string $line) => preg_replace('~^\\s*\\*~', '', $line),
            explode(PHP_EOL, $docBlock),
        ),
        fn(string $line) => !in_array($line, ['/**', '*/', '/'], true)
    );

    $descriptionLines = [];

    foreach ($textLines as $k => $line) {
        $line = str_starts_with($line, ' ') ? substr($line, 1) : $line;

        if (str_starts_with($line, '@example') || str_starts_with($line, '@see') || str_starts_with($line, '@link')) {
            continue;
        }

        if (str_starts_with($line, '@')) {
            break;
        }

        if (str_contains($line, '```')) {
            $line = trim($line);
        }

        $descriptionLines[] = $line;

        if (str_contains($line, '```php')) {
            if (isset($textLines[$k + 1]) && !str_contains($textLines[$k + 1], '<?php')) {
                $descriptionLines[] = '<?php';
            }
        }
    }

    return sprintf(
        "#### %s\nSignature: `%s(%s)` : `%s`%s",
        $m->getName(),
        $m->getName(),
        $parametersString,
        $returnType,
        count($descriptionLines) ? "  \n\n" . implode("\n", $descriptionLines) : ''
    );
}, $apiClassMethods);

$methodsMarkdown = array_map(fn(string $m) => PHP_EOL . $m, $methods);

$destFileLines = file($destFile);

// Remove any content between the `<!-- methods -->` and `<!-- /methods -->` markers.
$methodsStart = array_search('<!-- methods -->' . PHP_EOL, $destFileLines, true);
$methodsEnd = array_search('<!-- /methods -->' . PHP_EOL, $destFileLines, true);
$methodsLines = array_merge(
    array_slice($destFileLines, 0, $methodsStart + 1),
    array_slice($destFileLines, $methodsEnd)
);

// Insert the methods markdown between the `<!-- methods -->` and `<!-- /methods -->` markers.
$methodsStart = array_search('<!-- methods -->' . PHP_EOL, $destFileLines, true);
$methodsEnd = array_search('<!-- /methods -->' . PHP_EOL, $destFileLines, true);
$methodsLines = array_merge(
    array_slice($destFileLines, 0, $methodsStart + 1),
    $methodsMarkdown,
    array_slice($destFileLines, $methodsEnd)
);
file_put_contents($destFile, implode('', $methodsLines));
