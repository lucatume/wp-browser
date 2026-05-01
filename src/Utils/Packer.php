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
    public const CLOSURE_PROTOCOL = 'wpbrowser-closure';
    private const ARROW_FUNC_PATTERN = '/((static\s+)?fn\s*\([^)]*\)\s*(?::\s*\S+\s*)?=>[^;]+)/s';
    private const FUNC_PATTERN = '/((static\s+)?function\s*\([^)]*\)\s*(?::\s*\S+\s*)?(?:use\s*\([^)]*\))?\s*\{.*})/s';
    private const FUNC_START_PATTERN =
        '/((static\s+)?function\s*\([^)]*\)\s*(?::\s*\S+\s*)?(?:use\s*\([^)]*\))?\s*\{)$/';

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

        $wrapperRegistered = in_array(self::CLOSURE_PROTOCOL, stream_get_wrappers(), true);
        if (!$wrapperRegistered) {
            stream_wrapper_register(self::CLOSURE_PROTOCOL, ClosureStreamWrapper::class);
        }

        try {
            /** @var array{type: string, value: mixed} $data */
            return $this->unpackValue($data);
        } finally {
            if (!$wrapperRegistered) {
                stream_wrapper_unregister(self::CLOSURE_PROTOCOL);
            }
        }
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
                if ($this->nullifyClosures) {
                    return [
                        'type' => 'closure',
                        'value' => null,
                    ];
                }
                return [
                    'type' => 'closure',
                    'value' => $this->packClosure($value),
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
            throw new PackerException("Class not found: $className");
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
    private function packClosure(Closure $closure): array
    {
        $reflection = new ReflectionFunction($closure);

        $filename = $reflection->getFileName();
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();

        if ($filename && $startLine && $endLine) {
            $source = file($filename);
            if ($source === false) {
                $code = '';
            } else {
                $lines = array_slice($source, $startLine - 1, $endLine - $startLine + 1);
                $code = implode('', $lines);
            }

            if (preg_match(self::ARROW_FUNC_PATTERN, $code, $arrowMatches)) {
                $code = $arrowMatches[1];
            } elseif (preg_match(self::FUNC_PATTERN, $code, $matches)) {
                $code = $matches[1];

                $braceCount = 0;
                $inClosure = false;
                $result = '';
                foreach (str_split($code) as $i => $char) {
                    if (!$inClosure && $char === '{') {
                        $precedingCode = substr($code, 0, $i + 1);
                        if (preg_match(self::FUNC_START_PATTERN, $precedingCode)) {
                            $inClosure = true;
                            $braceCount = 1;
                            preg_match(self::FUNC_START_PATTERN, $precedingCode, $functionStart);
                            $result = $functionStart[1];

                            continue;
                        }
                    }

                    if ($inClosure) {
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
                }

                $code = $result ?: $code;
            }
        } else {
            $code = '';
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
            $useContext = str_replace('\\', '\\\\', $value['useContext']);
            $code .= "extract(\$packer->unpack('{$useContext}'));" . PHP_EOL;

            if (str_contains($value['useContext'], '@closureReference')
                && preg_match('/@closureReference\((?<closureName>\\w+)\)/', $value['useContext'], $matches) === 1
            ) {
                $closureName = $matches['closureName'];
                $code .= "\${$closureName} = ";
            }
        }

        $code .= "\$closure = {$closureCode};" . PHP_EOL;

        if ($value['closureThis'] !== null) {
            $closureThis = str_replace('\\', '\\\\', $value['closureThis']);
            $code .= "\$closureThis = \$packer->unpack('{$closureThis}');" . PHP_EOL;
        } else {
            $code .= '$closureThis = null;' . PHP_EOL;
        }

        $code .= 'return \Closure::bind($closure, $closureThis, null);';

        $closurePayload = base64_encode($code);

        $result = include self::CLOSURE_PROTOCOL . "://{$closurePayload}";
        if (!$result instanceof Closure) {
            throw new PackerException('Failed to unpack closure');
        }

        return $result;
    }

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
    private static function isStaticClosure(Closure $closure): bool
    {
        self::$bindSentinel ??= new stdClass();
        return @Closure::bind($closure, self::$bindSentinel) === null;
    }
}
