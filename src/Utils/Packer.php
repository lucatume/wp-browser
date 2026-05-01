<?php

declare(strict_types=1);

namespace lucatume\WPBrowser\Utils;

use Closure;
use JsonException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use stdClass;
use Throwable;

final class Packer
{
    private const JS_MAX_SAFE_INTEGER = 9007199254740991;
    private const FUNC_PATTERN = '/((static\s+)?function\s*\([^)]*\)\s*(?:use\s*\([^)]*\))?\s*(?::\s*\S+\s*)?\{.*})/s';
    private const FUNC_HEADER_PATTERN =
        '/^(static\s+)?function\s*\([^)]*\)\s*(?:use\s*\([^)]*\))?\s*(?::\s*\S+\s*)?\{/';

    /**
     * @var array<string, string>
     */
    private array $packReferences = [];

    private int $packRefCounter = 0;

    /**
     * @var array<string, mixed>
     */
    private array $unpackReferences = [];

    public function __construct(private bool $nullifyClosures = false)
    {
    }

    /**
     * @throws PackerException
     */
    public function pack(mixed $value): string
    {
        $this->packReferences = [];
        $this->packRefCounter = 0;

        try {
            $packed = $this->packValue($value);

            return json_encode($packed, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new PackerException('Failed to encode value: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws PackerException
     */
    public function unpack(string $value): mixed
    {
        $this->unpackReferences = [];

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($decoded)) {
                throw new PackerException('Failed to decode value: invalid format');
            }
            $data = $decoded;
        } catch (JsonException $e) {
            throw new PackerException('Failed to decode value: ' . $e->getMessage(), 0, $e);
        }

        if (!isset($data['type']) || !is_string($data['type'])) {
            throw new PackerException('Invalid packed format: missing or invalid type field');
        }

        // Register the wrapper for the lifetime of the process. PHPUnit Util\Filter,
        // MonkeyPatch FileStreamWrapper::url_stat, and exception pretty-printers walk
        // stack frames and call is_file() / file_exists() on every entry; if a frame's
        // `file` is `closure://...` and the wrapper is not registered, those probes
        // emit warnings that error handlers convert to fatal exceptions.
        if (!in_array('closure', stream_get_wrappers(), true)) {
            stream_wrapper_register('closure', ClosureStreamWrapper::class);
        }

        /** @var array{type: string, value: mixed} $data */
        return $this->unpackValue($data);
    }

    /**
     * @return array{type: string, value: mixed}
     */
    private function packValue(mixed $value): array
    {
        $type = gettype($value);

        if ($type === 'object') {
            assert(is_object($value));
            $objHash = spl_object_hash($value);
            if (isset($this->packReferences[$objHash])) {
                return [
                    'type' => 'reference',
                    'value' => $this->packReferences[$objHash],
                ];
            }
            $this->packReferences[$objHash] = '@ref_' . $this->packRefCounter++;

            if ($value instanceof Closure) {
                // Process-level cycle break: if THIS exact closure is already being packed
                // anywhere up the call stack (e.g. via $closureThis containing a property
                // that arrays back to this closure), nullify it here to terminate the
                // recursion. The outermost pack of the closure still produces the full
                // representation; only the nested re-entries get nullified.
                if ($this->nullifyClosures || isset(self::$packingClosures[$objHash])) {
                    // Drop the @ref we just allocated so a later encounter re-nullifies
                    // instead of returning a reference the unpacker can't resolve (the
                    // nullified marker carries no @ref entry on the unpack side).
                    unset($this->packReferences[$objHash]);
                    return [
                        'type' => 'closure',
                        'value' => null,
                    ];
                }
                return [
                    'type' => 'closure',
                    'value' => $this->packClosure($value, $objHash),
                ];
            }

            if ($value instanceof Throwable) {
                return $this->packException($value, $objHash);
            }

            return $this->packObject($value, $objHash);
        }

        return match ($type) {
            'boolean' => (function () use ($value) {
                assert(is_bool($value));
                return $this->packBoolean($value);
            })(),
            'integer' => (function () use ($value) {
                assert(is_int($value));
                return $this->packInteger($value);
            })(),
            'double' => (function () use ($value) {
                assert(is_float($value));
                return $this->packFloat($value);
            })(),
            'string' => (function () use ($value) {
                assert(is_string($value));
                return $this->packString($value);
            })(),
            'NULL' => $this->packNull(),
            'resource', 'resource (closed)' => $this->packResource(),
            'array' => (function () use ($value) {
                assert(is_array($value));
                return $this->packArray($value);
            })(),
            default => throw new PackerException("Unsupported type: $type"),
        };
    }

    /**
     * @return array{type: 'boolean', value: bool}
     */
    private function packBoolean(bool $value): array
    {
        return [
            'type' => 'boolean',
            'value' => $value,
        ];
    }

    /**
     * @return array{type: 'integer', value: int|string}
     */
    private function packInteger(int $value): array
    {
        return [
            'type' => 'integer',
            'value' => abs($value) > self::JS_MAX_SAFE_INTEGER ? (string)$value : $value,
        ];
    }

    /**
     * @return array{type: 'float', value: float|string}
     */
    private function packFloat(float $value): array
    {
        if (is_nan($value)) {
            return [
                'type' => 'float',
                'value' => 'NAN',
            ];
        }
        if ($value === INF) {
            return [
                'type' => 'float',
                'value' => 'INF',
            ];
        }
        if ($value === -INF) {
            return [
                'type' => 'float',
                'value' => '-INF',
            ];
        }
        return [
            'type' => 'float',
            'value' => $value,
        ];
    }

    /**
     * @return array{type: 'string', value: string}
     */
    private function packString(string $value): array
    {
        return [
            'type' => 'string',
            'value' => $value,
        ];
    }

    /**
     * @return array{type: 'null', value: null}
     */
    private function packNull(): array
    {
        return [
            'type' => 'null',
            'value' => null,
        ];
    }

    /**
     * @return array{type: 'resource', value: null}
     */
    private function packResource(): array
    {
        return [
            'type' => 'resource',
            'value' => null,
        ];
    }

    /**
     * @param array<int|string, mixed> $value
     * @return array{type: 'array', value: array<int|string, array{type: string, value: mixed}>}
     */
    private function packArray(array $value): array
    {
        $packed = [];
        $isAssoc = $this->isAssociative($value);

        foreach ($value as $key => $item) {
            if ($isAssoc) {
                $packed[$key] = $this->packValue($item);
            } else {
                $packed[] = $this->packValue($item);
            }
        }

        return [
            'type' => 'array',
            'value' => $packed,
        ];
    }

    /**
     * @return array{type: 'object', value: array<string, mixed>}
     */
    private function packObject(object $value, string $objHash): array
    {
        $class = get_class($value);
        $packedObj = [
            '@class' => $class,
            '@ref' => $this->packReferences[$objHash],
        ];

        $reflection = new ReflectionClass($value);

        if ($value instanceof stdClass) {
            foreach (get_object_vars($value) as $propName => $propValue) {
                $packedObj[$propName] = $this->packValue($propValue);
            }
        } else {
            $allProperties = [];
            $currentClass = $reflection;

            while ($currentClass !== false) {
                foreach ($currentClass->getProperties() as $prop) {
                    if ($prop->isStatic()) {
                        continue;
                    }
                    $propName = $prop->getName();
                    if (!isset($allProperties[$propName])) {
                        $prop->setAccessible(true);
                        $allProperties[$propName] = true;
                        try {
                            if ($prop->isInitialized($value)) {
                                $packedObj[$propName] = $this->packValue($prop->getValue($value));
                            } else {
                                $packedObj[$propName] = ['type' => 'null', 'value' => null];
                            }
                        } catch (\Error $e) {
                            $packedObj[$propName] = ['type' => 'null', 'value' => null];
                        }
                    }
                }
                $currentClass = $currentClass->getParentClass();
            }

            foreach (get_object_vars($value) as $propName => $propValue) {
                if (!isset($allProperties[$propName])) {
                    $packedObj[$propName] = $this->packValue($propValue);
                }
            }
        }

        return [
            'type' => 'object',
            'value' => $packedObj,
        ];
    }

    /**
     * @return array{type: 'object', value: array<string, mixed>}
     */
    private function packException(Throwable $value, string $objHash): array
    {
        $class = get_class($value);
        $packedObj = [
            '@class' => $class,
            '@ref' => $this->packReferences[$objHash],
        ];

        $reflection = new ReflectionClass($value);

        foreach ($reflection->getProperties() as $prop) {
            if ($prop->isStatic()) {
                continue;
            }
            $prop->setAccessible(true);
            $propName = $prop->getName();

            if ($propName === 'trace') {
                $trace = $prop->getValue($value);
                $modifiedTrace = [];
                if (is_array($trace)) {
                    foreach ($trace as $entry) {
                        if (is_array($entry)) {
                            $entry['object'] = null;
                            $entry['args'] = null;
                            $modifiedTrace[] = $entry;
                        }
                    }
                }
                $packedObj[$propName] = $this->packValue($modifiedTrace);
            } else {
                $packedObj[$propName] = $this->packValue($prop->getValue($value));
            }
        }

        return [
            'type' => 'object',
            'value' => $packedObj,
        ];
    }

    /**
     * @param array{type: string, value: mixed} $data
     */
    private function unpackValue(array $data): mixed
    {
        $type = $data['type'];
        $value = $data['value'];

        if ($type === 'reference') {
            return $this->unpackReference($value);
        }

        if ($type === 'closure') {
            if ($value === null) {
                return null;
            }

            if (!is_array($value)) {
                throw new PackerException('Closure value must be an array');
            }

            /** @var array{code: string, static: bool, useContext: string|null, closureThis: string|null, closureCalledClass: string|null, closureScopedClass: string|null} $value */
            return $this->unpackClosure($value);
        }

        return match ($type) {
            'boolean' => $this->unpackBoolean($value),
            'string' => $this->unpackString($value),
            'integer' => $this->unpackInteger($value),
            'float' => $this->unpackFloat($value),
            'null' => $this->unpackNull(),
            'resource' => $this->unpackResource(),
            'array' => $this->unpackArray($value),
            'object' => $this->unpackObject($value),
            default => throw new PackerException("Unknown type: {$type}"),
        };
    }

    private function unpackBoolean(mixed $value): bool
    {
        return is_bool($value) ? $value : (bool)$value;
    }

    private function unpackString(mixed $value): string
    {
        if (!is_string($value)) {
            throw new PackerException('Expected string value');
        }
        return $value;
    }

    private function unpackInteger(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value)) {
            return (int)$value;
        }
        throw new PackerException('Expected integer value');
    }

    private function unpackFloat(mixed $value): float
    {
        if ($value === 'NAN') {
            return NAN;
        }
        if ($value === 'INF') {
            return INF;
        }
        if ($value === '-INF') {
            return -INF;
        }
        if (is_float($value)) {
            return $value;
        }
        if (is_int($value)) {
            return (float)$value;
        }
        throw new PackerException('Expected float value');
    }

    /**
     * @return null
     */
    private function unpackNull()
    {
        return null;
    }

    /**
     * @return null
     */
    private function unpackResource()
    {
        return null;
    }

    /**
     * @param mixed $value
     * @return array<int|string, mixed>
     */
    private function unpackArray(mixed $value): array
    {
        if (!is_array($value)) {
            throw new PackerException('Invalid array value');
        }

        $result = [];

        foreach ($value as $key => $item) {
            if ($this->isPackedValue($item)) {
                $result[$key] = $this->unpackValue($item);
            } else {
                $result[$key] = $item;
            }
        }

        return $result;
    }

    /**
     * @param mixed $value
     */
    private function unpackObject(mixed $value): object
    {
        if (!is_array($value) || !isset($value['@class'])) {
            throw new PackerException('Invalid object format');
        }

        $className = $value['@class'];
        if (!is_string($className)) {
            throw new PackerException('Class name must be a string');
        }

        if (!class_exists($className)) {
            return $this->unpackIncompleteClass($className, $value);
        }

        $reflection = new ReflectionClass($className);
        $instance = $reflection->newInstanceWithoutConstructor();

        if (isset($value['@ref']) && is_string($value['@ref'])) {
            $this->unpackReferences[$value['@ref']] = $instance;
        }

        foreach ($value as $key => $propValue) {
            if (!is_string($key) || $key === '@class' || $key === '@ref') {
                continue;
            }

            $propertySet = false;
            $currentClass = $reflection;

            while ($currentClass !== false) {
                if ($currentClass->hasProperty($key)) {
                    try {
                        $prop = $currentClass->getProperty($key);
                        if ($prop->isStatic()) {
                            $propertySet = true;
                            break;
                        }
                        $prop->setAccessible(true);
                        if ($this->isPackedValue($propValue)) {
                            $prop->setValue($instance, $this->unpackValue($propValue));
                        } else {
                            $prop->setValue($instance, $propValue);
                        }
                        $propertySet = true;
                        break;
                    } catch (ReflectionException) {
                        // Property might be typed and not accept the value; fallback to dynamic property.
                    } catch (\TypeError) {
                        // Typed property rejected null/value — original was uninitialized.
                        // Treat as set so we don't pollute via dynamic property fallback.
                        $propertySet = true;
                        break;
                    }
                }
                $currentClass = $currentClass->getParentClass();
            }

            if (!$propertySet) {
                if ($this->isPackedValue($propValue)) {
                    $instance->$key = $this->unpackValue($propValue);
                } else {
                    $instance->$key = $propValue;
                }
            }
        }

        return $instance;
    }

    /**
     * Mirror PHP's native serialize/unserialize behavior for missing classes:
     * return a __PHP_Incomplete_Class instance with the original class name and
     * properties preserved. This keeps closure unpacks alive in worker / fork
     * processes that cannot see PHPUnit-generated Mock_<Class>_<hex> classes
     * present only in the test process. The body using the closure is free to
     * fail on access; the unpack itself does not.
     *
     * Built via unserialize() of a hand-crafted O:n:"X" payload — PHP itself
     * materializes a __PHP_Incomplete_Class for any class name it cannot resolve,
     * and that path is the only way to write to __PHP_Incomplete_Class_Name and
     * to undeclared dynamic properties (a Closure cannot ::call() into the scope
     * of an internal class, and direct $instance->prop = ... emits a warning
     * about overloaded properties on __PHP_Incomplete_Class).
     *
     * @param array<int|string, mixed> $value
     */
    private function unpackIncompleteClass(string $className, array $value): object
    {
        // Build a synthetic serialize() payload for a missing class: PHP's unserialize
        // materializes those into __PHP_Incomplete_Class with __PHP_Incomplete_Class_Name
        // set automatically, and that path is the only one that lets us populate the
        // proxy's metadata + arbitrary properties. A Closure cannot ->call() into
        // the scope of an internal class, and a direct $instance->prop = … on
        // __PHP_Incomplete_Class emits an "overloaded property" warning.
        $props = [];
        foreach ($value as $key => $propValue) {
            if (!is_string($key) || $key === '@class' || $key === '@ref') {
                continue;
            }
            $props[$key] = $this->isPackedValue($propValue) ? $this->unpackValue($propValue) : $propValue;
        }

        $payload = sprintf('O:%d:"%s":%d:{', strlen($className), $className, count($props));
        foreach ($props as $key => $val) {
            $payload .= sprintf('s:%d:"%s";', strlen($key), $key) . serialize($val);
        }
        $payload .= '}';

        $instance = unserialize($payload);
        if (!$instance instanceof \__PHP_Incomplete_Class) {
            throw new PackerException("Failed to materialize incomplete class proxy for $className");
        }

        if (isset($value['@ref']) && is_string($value['@ref'])) {
            $this->unpackReferences[$value['@ref']] = $instance;
        }

        return $instance;
    }

    private function unpackReference(mixed $value): mixed
    {
        if (!is_string($value) || !isset($this->unpackReferences[$value])) {
            $refValue = is_string($value) ? $value : 'non-string';
            throw new PackerException("Invalid reference: {$refValue}");
        }

        return $this->unpackReferences[$value];
    }

    /**
     * @param array<int|string, mixed> $arr
     */
    private function isAssociative(array $arr): bool
    {
        if (empty($arr)) {
            return false;
        }

        // array_is_list() is implemented in C and is PHP 8.1+. Use it when available;
        // fall back to an inline early-exit foreach on PHP 8.0.
        if (function_exists('array_is_list')) {
            return !array_is_list($arr);
        }

        $i = 0;
        foreach ($arr as $key => $_) {
            if ($key !== $i++) {
                return true;
            }
        }
        return false;
    }

    /**
     * @phpstan-assert-if-true array{type: string, value: mixed} $value
     */
    private function isPackedValue(mixed $value): bool
    {
        return is_array($value) && isset($value['type']) && is_string($value['type']);
    }

    /**
     * @return array{
     *     code: string,
     *     static: bool,
     *     useContext: string|null,
     *     closureThis: string|null,
     *     closureCalledClass: string|null,
     *     closureScopedClass: string|null
     * }
     *
     * @throws PackerException
     * @throws ReflectionException
     */
    /**
     * Extract a single arrow-function expression starting somewhere inside `$code`.
     *
     * The naive regex `=>[^;]+` over-captures when an arrow function is inline inside an
     * outer call like `->addClosure(fn() => ...)` — the `;` terminator belongs to the
     * outer statement, so the match swallows the trailing `)`. Walk the expression
     * tracking paren / bracket depth and stop at the first top-level `,`, `)`, `]`, or
     * `;`. Returns null if no arrow function is found.
     */
    private function extractArrowFunction(string $code): ?string
    {
        if (!preg_match('/(static\s+)?fn\s*\([^)]*\)\s*(?::\s*\S+\s*)?=>/', $code, $headerMatch, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $start = $headerMatch[0][1];
        $headerEnd = $start + strlen($headerMatch[0][0]);
        $len = strlen($code);
        $depth = 0;
        $i = $headerEnd;
        while ($i < $len) {
            $ch = $code[$i];
            if ($ch === '(' || $ch === '[' || $ch === '{') {
                $depth++;
            } elseif ($ch === ')' || $ch === ']' || $ch === '}') {
                if ($depth === 0) {
                    break;
                }
                $depth--;
            } elseif ($depth === 0 && ($ch === ',' || $ch === ';')) {
                break;
            }
            $i++;
        }
        return rtrim(substr($code, $start, $i - $start));
    }

    /**
     * @return array{
     *     code: string,
     *     static: bool,
     *     useContext: string|null,
     *     closureThis: string|null,
     *     closureCalledClass: string|null,
     *     closureScopedClass: string|null
     * }
     *
     * @throws PackerException
     * @throws ReflectionException
     */
    private function packClosure(Closure $closure, string $objHash): array
    {
        self::$packingClosures[$objHash] = true;
        try {
            return $this->doPackClosure($closure);
        } finally {
            unset(self::$packingClosures[$objHash]);
        }
    }

    /**
     * @return array{
     *     code: string,
     *     static: bool,
     *     useContext: string|null,
     *     closureThis: string|null,
     *     closureCalledClass: string|null,
     *     closureScopedClass: string|null
     * }
     *
     * @throws PackerException
     * @throws ReflectionException
     */
    private function doPackClosure(Closure $closure): array
    {
        $reflection = new ReflectionFunction($closure);

        $filename = $reflection->getFileName();
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();
        if ($filename === false || $filename === '' || !is_file($filename) || !is_readable($filename)) {
            throw new PackerException(
                'Cannot pack closure: source file is unavailable or unreadable '
                . '(closures created via eval/runtime code are not supported).'
            );
        }
        if ($startLine === false || $endLine === false || $startLine < 1 || $endLine < $startLine) {
            throw new PackerException(
                'Cannot pack closure: invalid source line information from reflection.'
            );
        }

        $source = file($filename);
        if ($source === false) {
            throw new PackerException("Cannot pack closure: failed to read source file {$filename}.");
        }

        $lines = array_slice($source, $startLine - 1, $endLine - $startLine + 1);
        $code = implode('', $lines);

        $arrowExtracted = $this->extractArrowFunction($code);
        if ($arrowExtracted !== null) {
            $code = $arrowExtracted;
        } elseif (preg_match(self::FUNC_PATTERN, $code, $matches)) {
            $code = $matches[1];

            if (preg_match(self::FUNC_HEADER_PATTERN, $code, $headerMatch)) {
                $header = $headerMatch[0];
                $headerLen = strlen($header);
                $codeLen = strlen($code);
                $result = $header;
                $braceCount = 1;
                for ($i = $headerLen; $i < $codeLen; $i++) {
                    $char = $code[$i];
                    $result .= $char;
                    if ($char === '{') {
                        $braceCount++;
                    } elseif ($char === '}') {
                        $braceCount--;
                        if ($braceCount === 0) {
                            break;
                        }
                    }
                }

                $code = $result;
            }
        }

        if ($code === '') {
            throw new PackerException(
                "Cannot pack closure: failed to extract closure source from {$filename}:{$startLine}-{$endLine}."
            );
        }

        $isStatic = self::isStaticClosure($closure);

        $staticVariables = $reflection->getStaticVariables();
        if (count($staticVariables)) {
            foreach ($staticVariables as $key => &$svar) {
                if ($svar === $closure) {
                    $svar = "@closureReference({$key})";
                    break;
                }
            }
            unset($svar);
            $useContext = $this->pack($staticVariables);
        } else {
            $useContext = null;
        }

        $closureThis = $reflection->getClosureThis();
        $packedThis = $closureThis !== null ? $this->pack($closureThis) : null;

        $closureCalledClass = $reflection->getClosureCalledClass();
        $closureScopeClass = $reflection->getClosureScopeClass();

        $code = self::rewriteClosureBody(
            $code,
            $filename,
            $startLine,
            $closureScopeClass ? $closureScopeClass->getName() : null
        );

        return [
            'code' => $code,
            'static' => $isStatic,
            'useContext' => $useContext,
            'closureThis' => $packedThis,
            'closureCalledClass' => $closureCalledClass ? $closureCalledClass->getName() : null,
            'closureScopedClass' => $closureScopeClass ? $closureScopeClass->getName() : null,
        ];
    }

    /**
     * @param array{
     *     code: string,
     *     static: bool,
     *     useContext: string|null,
     *     closureThis: string|null,
     *     closureCalledClass: string|null,
     *     closureScopedClass: string|null
     * } $value
     */
    private function unpackClosure(array $value): Closure
    {
        $closureCode = $value['code'];
        $packerClass = self::class;

        if ($value['closureCalledClass'] !== null) {
            $closureCode = str_replace('static::', "{$value['closureCalledClass']}::", $closureCode);
        }

        if ($value['closureScopedClass'] !== null) {
            $closureCode = str_replace('self::', "{$value['closureScopedClass']}::", $closureCode);
        }

        $code = '$packer = new \\' . $packerClass . '();' . PHP_EOL;

        if ($value['useContext'] !== null) {
            $useContextLiteral = var_export($value['useContext'], true);
            $code .= "extract(\$packer->unpack({$useContextLiteral}));" . PHP_EOL;

            if (str_contains($value['useContext'], '@closureReference')
                && preg_match('/@closureReference\((?<closureName>\\w+)\)/', $value['useContext'], $matches) === 1
            ) {
                $closureName = $matches['closureName'];
                $code .= "\${$closureName} = ";
            }
        }

        $code .= "\$closure = {$closureCode};" . PHP_EOL;

        if ($value['static']) {
            // Static closures cannot accept a $this binding; rebind to null and preserve scope only.
            $code .= '$closureThis = null;' . PHP_EOL;
        } elseif ($value['closureThis'] !== null) {
            $closureThisLiteral = var_export($value['closureThis'], true);
            $code .= "\$closureThis = \$packer->unpack({$closureThisLiteral});" . PHP_EOL;
        } else {
            $code .= '$closureThis = null;' . PHP_EOL;
        }

        $scopeArg = $value['closureScopedClass'] !== null
            ? var_export($value['closureScopedClass'], true)
            : 'null';
        $code .= "return \\Closure::bind(\$closure, \$closureThis, {$scopeArg});";

        $closurePayload = base64_encode($code);

        $result = include "closure://{$closurePayload}";
        if (!$result instanceof Closure) {
            throw new PackerException('Failed to unpack closure');
        }

        return $result;
    }

    /**
     * Process-level set of closure spl_object_hashes currently mid-pack. Spans nested
     * Packer instances (each closure's bindings are packed by a fresh inner Packer with
     * its own reference table) so that cyclic graphs — closure A whose $this contains
     * closure B whose $this references back to A — terminate via nullification rather
     * than infinite recursion.
     *
     * @var array<string, true>
     */
    private static array $packingClosures = [];

    private static ?stdClass $bindSentinel = null;

    /**
     * Detect whether a Closure was declared with the `static` keyword.
     *
     * Single codepath across PHP versions: Closure::bind() returns null when called on a
     * static closure. PHP also emits a "Cannot bind an instance to a static closure"
     * E_WARNING in that case — that warning is the documented signal we rely on, not a
     * bug, hence the `@` suppression. ReflectionFunctionAbstract::isStatic() only exists
     * on PHP 8.1+, so this trick is the only branchless option that works on PHP 8.0.
     */
    public static function isStaticClosure(Closure $closure): bool
    {
        self::$bindSentinel ??= new stdClass();
        // Closure::bind() emits an E_WARNING for static closures; the `@` suppression is
        // bypassed by custom error handlers (Codeception/PHPUnit convert E_WARNING to
        // ErrorException), so install a scoped no-op handler for the bind call.
        set_error_handler(static fn() => true);
        try {
            return Closure::bind($closure, self::$bindSentinel) === null;
        } finally {
            restore_error_handler();
        }
    }

    /**
     * @var array<string, array{0: string, 1: array<string, string>}>
     */
    private static array $sourceFileImportsCache = [];

    private const SKIP_PREV_FOR_USE_ALIAS = [
        T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_DOUBLE_COLON,
        T_FUNCTION, T_CONST, T_GOTO, T_AS,
    ];

    /**
     * PHPCS ships a polyfill that defines T_NAME_* as strings if undefined; that branch
     * never fires on PHP 8.0+ at runtime but PHPStan sees both definitions. Resolving
     * through constant() makes the comparison int === int from PHPStan's perspective.
     *
     * @var array{qualified: int, fullyQualified: int, relative: int}|null
     */
    private static ?array $nameTokenIds = null;

    /**
     * @return array{qualified: int, fullyQualified: int, relative: int}
     */
    private static function nameTokenIds(): array
    {
        if (self::$nameTokenIds !== null) {
            return self::$nameTokenIds;
        }
        $resolve = static function (string $name): int {
            $value = constant($name);
            return is_int($value) ? $value : -1;
        };
        return self::$nameTokenIds = [
            'qualified' => $resolve('T_NAME_QUALIFIED'),
            'fullyQualified' => $resolve('T_NAME_FULLY_QUALIFIED'),
            'relative' => $resolve('T_NAME_RELATIVE'),
        ];
    }

    private static function isTrivia(int $id): bool
    {
        return $id === T_WHITESPACE || $id === T_COMMENT || $id === T_DOC_COMMENT;
    }

    private static function isNamePartToken(int $id): bool
    {
        $ids = self::nameTokenIds();
        return $id === T_STRING
            || $id === T_NS_SEPARATOR
            || $id === $ids['qualified']
            || $id === $ids['fullyQualified'];
    }

    /**
     * Resolve short class names to FQCNs and substitute magic constants with literal values.
     *
     * The closure body is re-evaluated at unpack time inside a `closure://` stream URL with
     * no namespace and no `use` imports, so an unqualified class identifier like
     * `new InstallationException()` would fail with "Class not found", and `__DIR__` would
     * resolve against the URL instead of the source file. Both transforms happen here at
     * pack time, where reflection still gives us the source file path.
     */
    private static function rewriteClosureBody(
        string $body,
        string $filename,
        int $startLine,
        ?string $scopeClassName
    ): string {
        [$fileNamespace, $useMap] = self::parseSourceFileImports($filename);

        $tokens = @token_get_all('<?php ' . $body);
        if (!is_array($tokens) || count($tokens) === 0) {
            return $body;
        }

        array_shift($tokens);

        $output = '';
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $tok = $tokens[$i];

            if (is_string($tok)) {
                $output .= $tok;
                continue;
            }

            [$id, $text, $line] = $tok;

            switch ($id) {
                case T_FILE:
                    $output .= var_export($filename, true);
                    continue 2;
                case T_DIR:
                    $output .= var_export(dirname($filename), true);
                    continue 2;
                case T_LINE:
                    $output .= (string) ($startLine + $line - 1);
                    continue 2;
                case T_NS_C:
                    $output .= var_export($fileNamespace, true);
                    continue 2;
                case T_CLASS_C:
                    $output .= var_export($scopeClassName ?? '', true);
                    continue 2;
                case T_FUNC_C:
                    $output .= "'{closure}'";
                    continue 2;
                case T_METHOD_C:
                    $output .= var_export(
                        ($scopeClassName !== null ? $scopeClassName . '::' : '') . '{closure}',
                        true
                    );
                    continue 2;
                case T_TRAIT_C:
                    $output .= "''";
                    continue 2;
            }

            $nameIds = self::nameTokenIds();

            if ($id === T_STRING) {
                $prev = self::prevSignificantToken($tokens, $i);
                if (isset($useMap[$text])
                    && !in_array($prev, self::SKIP_PREV_FOR_USE_ALIAS, true)
                    && !($prev !== T_NEW && self::isCallParenAhead($tokens, $i))
                ) {
                    $output .= '\\' . $useMap[$text];
                    continue;
                }
                $output .= $text;
                continue;
            }

            if ($id === $nameIds['qualified']) {
                $parts = explode('\\', $text);
                $first = $parts[0];
                if (isset($useMap[$first])) {
                    $parts[0] = $useMap[$first];
                    $output .= '\\' . implode('\\', $parts);
                    continue;
                }
                $output .= $text;
                continue;
            }

            if ($id === $nameIds['relative']) {
                $rest = substr($text, strlen('namespace\\'));
                $output .= '\\' . ($fileNamespace !== '' ? $fileNamespace . '\\' : '') . $rest;
                continue;
            }

            $output .= $text;
        }

        return $output;
    }

    /**
     * @param array<int, array{0: int, 1: string, 2: int}|string> $tokens
     */
    private static function prevSignificantToken(array $tokens, int $index): ?int
    {
        for ($j = $index - 1; $j >= 0; $j--) {
            $t = $tokens[$j];
            if (is_string($t)) {
                return null;
            }
            if (self::isTrivia($t[0])) {
                continue;
            }
            return $t[0];
        }
        return null;
    }

    /**
     * @param array<int, array{0: int, 1: string, 2: int}|string> $tokens
     */
    private static function isCallParenAhead(array $tokens, int $index): bool
    {
        $count = count($tokens);
        for ($j = $index + 1; $j < $count; $j++) {
            $t = $tokens[$j];
            if (is_string($t)) {
                return $t === '(';
            }
            if (self::isTrivia($t[0])) {
                continue;
            }
            return false;
        }
        return false;
    }

    /**
     * @return array{0: string, 1: array<string, string>}
     */
    private static function parseSourceFileImports(string $filename): array
    {
        if (isset(self::$sourceFileImportsCache[$filename])) {
            return self::$sourceFileImportsCache[$filename];
        }

        $namespace = '';
        $useMap = [];

        $source = @file_get_contents($filename);
        if ($source === false) {
            return self::$sourceFileImportsCache[$filename] = [$namespace, $useMap];
        }

        $tokens = @token_get_all($source);
        if (!is_array($tokens)) {
            return self::$sourceFileImportsCache[$filename] = [$namespace, $useMap];
        }

        $count = count($tokens);
        $depth = 0;

        for ($i = 0; $i < $count; $i++) {
            $tok = $tokens[$i];

            if (is_string($tok)) {
                if ($tok === '{') {
                    $depth++;
                } elseif ($tok === '}') {
                    $depth--;
                }
                continue;
            }

            $id = $tok[0];

            if ($id === T_CURLY_OPEN || $id === T_DOLLAR_OPEN_CURLY_BRACES) {
                $depth++;
                continue;
            }

            if ($depth !== 0) {
                continue;
            }

            if ($id === T_CLASS || $id === T_INTERFACE || $id === T_TRAIT || $id === T_FUNCTION) {
                break;
            }

            if ($id === T_NAMESPACE && $namespace === '') {
                $j = $i + 1;
                $ns = '';
                while ($j < $count) {
                    $t = $tokens[$j];
                    if (is_array($t)) {
                        $tid = $t[0];
                        if ($tid === T_WHITESPACE) {
                            $j++;
                            continue;
                        }
                        if (self::isNamePartToken($tid)) {
                            $ns .= $t[1];
                            $j++;
                            continue;
                        }
                        break;
                    }
                    if ($t === ';' || $t === '{') {
                        if ($t === '{') {
                            $depth++;
                        }
                        break;
                    }
                    $j++;
                }
                $namespace = trim($ns, '\\');
                $i = $j;
                continue;
            }

            if ($id === T_USE) {
                $j = $i + 1;
                while ($j < $count && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                    $j++;
                }
                if ($j >= $count) {
                    break;
                }

                $next = $tokens[$j];
                if (is_array($next) && ($next[0] === T_FUNCTION || $next[0] === T_CONST)) {
                    while ($i < $count && !(is_string($tokens[$i]) && $tokens[$i] === ';')) {
                        $i++;
                    }
                    continue;
                }

                self::collectClassUseAliases($tokens, $j, $useMap);
                while ($i < $count && !(is_string($tokens[$i]) && $tokens[$i] === ';')) {
                    $i++;
                }
            }
        }

        return self::$sourceFileImportsCache[$filename] = [$namespace, $useMap];
    }

    /**
     * @param array<int, array{0: int, 1: string, 2: int}|string> $tokens
     * @param array<string, string>                              $useMap
     */
    private static function collectClassUseAliases(array $tokens, int $start, array &$useMap): void
    {
        $count = count($tokens);
        $name = '';
        $j = $start;

        while ($j < $count) {
            $t = $tokens[$j];

            if (is_string($t)) {
                if ($t === ';') {
                    break;
                }
                if ($t === '{') {
                    self::collectGroupedUseAliases($tokens, $j + 1, trim($name, '\\') . '\\', $useMap);
                    return;
                }
                $j++;
                continue;
            }

            $tid = $t[0];
            if (self::isTrivia($tid)) {
                $j++;
                continue;
            }

            if ($tid === T_AS) {
                $aliasIndex = self::nextSignificantIndex($tokens, $j + 1);
                if ($aliasIndex !== null
                    && is_array($tokens[$aliasIndex])
                    && $tokens[$aliasIndex][0] === T_STRING
                ) {
                    $useMap[$tokens[$aliasIndex][1]] = trim($name, '\\');
                }
                return;
            }

            if (self::isNamePartToken($tid)) {
                $name .= $t[1];
                $j++;
                continue;
            }

            $j++;
        }

        self::registerSimpleUseAlias($name, '', $useMap);
    }

    /**
     * @param array<int, array{0: int, 1: string, 2: int}|string> $tokens
     * @param array<string, string>                              $useMap
     */
    private static function collectGroupedUseAliases(array $tokens, int $start, string $prefix, array &$useMap): int
    {
        $count = count($tokens);
        $name = '';
        $j = $start;

        while ($j < $count) {
            $t = $tokens[$j];

            if (is_string($t)) {
                if ($t === '}' || $t === ',') {
                    self::registerSimpleUseAlias($name, $prefix, $useMap);
                    $name = '';
                    if ($t === '}') {
                        return $j;
                    }
                    $j++;
                    continue;
                }
                $j++;
                continue;
            }

            $tid = $t[0];
            if (self::isTrivia($tid)) {
                $j++;
                continue;
            }

            if ($tid === T_AS) {
                $aliasIndex = self::nextSignificantIndex($tokens, $j + 1);
                if ($aliasIndex !== null
                    && is_array($tokens[$aliasIndex])
                    && $tokens[$aliasIndex][0] === T_STRING
                ) {
                    $useMap[$tokens[$aliasIndex][1]] = $prefix . trim($name, '\\');
                    $j = $aliasIndex + 1;
                } else {
                    $j++;
                }
                $name = '';
                continue;
            }

            if (self::isNamePartToken($tid)) {
                $name .= $t[1];
                $j++;
                continue;
            }

            $j++;
        }

        return $j;
    }

    /**
     * @param array<string, string> $useMap
     */
    private static function registerSimpleUseAlias(string $name, string $prefix, array &$useMap): void
    {
        $name = trim($name, '\\');
        if ($name === '') {
            return;
        }
        $fqcn = $prefix . $name;
        $parts = explode('\\', $fqcn);
        $alias = end($parts);
        $useMap[$alias] = $fqcn;
    }

    /**
     * @param array<int, array{0: int, 1: string, 2: int}|string> $tokens
     */
    private static function nextSignificantIndex(array $tokens, int $start): ?int
    {
        $count = count($tokens);
        for ($j = $start; $j < $count; $j++) {
            $t = $tokens[$j];
            if (is_string($t) || !self::isTrivia($t[0])) {
                return $j;
            }
        }
        return null;
    }
}
