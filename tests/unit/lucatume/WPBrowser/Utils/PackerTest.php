<?php

declare(strict_types=1);

namespace Unit\lucatume\WPBrowser\Utils;

use Codeception\Test\Unit;
use Exception;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Filesystem;
use lucatume\WPBrowser\Utils\Filesystem as FsAlias;
use lucatume\WPBrowser\Utils\Packer;
use lucatume\WPBrowser\Utils\PackerException;
use stdClass;

class PackerTest extends Unit
{
    use TmpFilesCleanup;

    public function test_packs_integer(): void
    {
        $packer = new Packer();

        $packed = $packer->pack(42);

        $decoded = json_decode($packed, true);
        $this->assertSame(['type' => 'integer', 'value' => 42], $decoded);
    }

    public function test_packs_float(): void
    {
        $packer = new Packer();

        $packed = $packer->pack(3.14);

        $decoded = json_decode($packed, true);
        $this->assertSame(['type' => 'float', 'value' => 3.14], $decoded);
    }

    public function test_packs_string(): void
    {
        $packer = new Packer();

        $packed = $packer->pack('hello');

        $decoded = json_decode($packed, true);
        $this->assertSame(['type' => 'string', 'value' => 'hello'], $decoded);
    }

    public function test_packs_boolean_true(): void
    {
        $packer = new Packer();

        $packed = $packer->pack(true);

        $decoded = json_decode($packed, true);
        $this->assertSame(['type' => 'boolean', 'value' => true], $decoded);
    }

    public function test_packs_boolean_false(): void
    {
        $packer = new Packer();

        $packed = $packer->pack(false);

        $decoded = json_decode($packed, true);
        $this->assertSame(['type' => 'boolean', 'value' => false], $decoded);
    }

    public function test_packs_null(): void
    {
        $packer = new Packer();

        $packed = $packer->pack(null);

        $decoded = json_decode($packed, true);
        $this->assertSame(['type' => 'null', 'value' => null], $decoded);
    }

    public function test_packs_sequential_array(): void
    {
        $packer = new Packer();

        $packed = $packer->pack([1, 'two', 3.14]);

        $decoded = json_decode($packed, true);
        $this->assertSame('array', $decoded['type']);
        $this->assertSame(['type' => 'integer', 'value' => 1], $decoded['value'][0]);
        $this->assertSame(['type' => 'string', 'value' => 'two'], $decoded['value'][1]);
        $this->assertSame(['type' => 'float', 'value' => 3.14], $decoded['value'][2]);
    }

    public function test_packs_associative_array(): void
    {
        $packer = new Packer();

        $packed = $packer->pack(['a' => 1, 'b' => 'two']);

        $decoded = json_decode($packed, true);
        $this->assertSame('array', $decoded['type']);
        $this->assertSame(['type' => 'integer', 'value' => 1], $decoded['value']['a']);
        $this->assertSame(['type' => 'string', 'value' => 'two'], $decoded['value']['b']);
    }

    public function test_packs_stdClass_object(): void
    {
        $packer = new Packer();
        $obj = new stdClass();
        $obj->name = 'test';
        $obj->value = 42;

        $packed = $packer->pack($obj);

        $decoded = json_decode($packed, true);
        $this->assertSame('object', $decoded['type']);
        $this->assertSame('stdClass', $decoded['value']['@class']);
        $this->assertSame(['type' => 'string', 'value' => 'test'], $decoded['value']['name']);
        $this->assertSame(['type' => 'integer', 'value' => 42], $decoded['value']['value']);
    }

    public function test_packs_custom_object(): void
    {
        $packer = new Packer();
        $obj = new TestCustomObject('test', 42);

        $packed = $packer->pack($obj);

        $decoded = json_decode($packed, true);
        $this->assertSame('object', $decoded['type']);
        $this->assertSame(TestCustomObject::class, $decoded['value']['@class']);
        $this->assertSame(['type' => 'string', 'value' => 'test'], $decoded['value']['publicName']);
        $this->assertSame(['type' => 'integer', 'value' => 42], $decoded['value']['privateValue']);
    }

    public function test_packs_circular_reference(): void
    {
        $packer = new Packer();
        $obj = new stdClass();
        $obj->name = 'parent';
        $obj->self = $obj;

        $packed = $packer->pack($obj);

        $decoded = json_decode($packed, true);
        $this->assertSame('object', $decoded['type']);
        $this->assertArrayHasKey('@ref', $decoded['value']);
        $this->assertSame('reference', $decoded['value']['self']['type']);
        $this->assertSame($decoded['value']['@ref'], $decoded['value']['self']['value']);
    }

    public function test_packs_resource_as_null(): void
    {
        $packer = new Packer();
        $tmpDir = Filesystem::tmpDir();
        $file = $tmpDir . '/test.txt';
        file_put_contents($file, 'test');
        $resource = fopen($file, 'r');

        $packed = $packer->pack($resource);

        fclose($resource);
        $decoded = json_decode($packed, true);
        $this->assertSame(['type' => 'resource', 'value' => null], $decoded);
    }

    public function test_packs_closed_resource_as_null(): void
    {
        $packer = new Packer();
        $tmpDir = Filesystem::tmpDir();
        $file = $tmpDir . '/test.txt';
        file_put_contents($file, 'test');
        $resource = fopen($file, 'r');
        fclose($resource);

        $packed = $packer->pack($resource);

        $decoded = json_decode($packed, true);
        $this->assertSame(['type' => 'resource', 'value' => null], $decoded);
    }

    public function test_packs_exception(): void
    {
        $packer = new Packer();
        $exception = new Exception('Test error', 123);

        $packed = $packer->pack($exception);

        $decoded = json_decode($packed, true);
        $this->assertSame('object', $decoded['type']);
        $this->assertSame(Exception::class, $decoded['value']['@class']);
        $this->assertSame(['type' => 'string', 'value' => 'Test error'], $decoded['value']['message']);
        $this->assertSame(['type' => 'integer', 'value' => 123], $decoded['value']['code']);
        $this->assertArrayHasKey('trace', $decoded['value']);

        foreach ($decoded['value']['trace']['value'] as $frame) {
            if (isset($frame['value']['object'])) {
                $this->assertSame(['type' => 'null', 'value' => null], $frame['value']['object']);
            }
            if (isset($frame['value']['args'])) {
                $this->assertSame(['type' => 'null', 'value' => null], $frame['value']['args']);
            }
        }
    }

    public function test_packs_simple_closure(): void
    {
        $packer = new Packer();
        $closure = static function () {
            return 42;
        };

        $packed = $packer->pack($closure);

        $decoded = json_decode($packed, true);
        $this->assertSame('closure', $decoded['type']);
        $this->assertArrayHasKey('code', $decoded['value']);
        $this->assertStringContainsString('return 42', $decoded['value']['code']);
    }

    public function test_pack_throws_clear_exception_for_eval_closure(): void
    {
        $packer = new Packer();
        $closure = eval('return static function (): int { return 42; };');

        $this->assertInstanceOf(\Closure::class, $closure);
        $this->expectException(PackerException::class);
        $this->expectExceptionMessage('source file is unavailable or unreadable');

        $packer->pack($closure);
    }

    public function test_packs_closure_with_parameters(): void
    {
        $packer = new Packer();
        $closure = static function (int $a, string $b): string {
            return $b . $a;
        };

        $packed = $packer->pack($closure);

        $decoded = json_decode($packed, true);
        $this->assertSame('closure', $decoded['type']);
        $this->assertStringContainsString('int $a', $decoded['value']['code']);
        $this->assertStringContainsString('string $b', $decoded['value']['code']);
    }

    public function test_packs_closure_with_use_statement(): void
    {
        $packer = new Packer();
        $captured = 'captured value';
        $closure = static function () use ($captured) {
            return $captured;
        };

        $packed = $packer->pack($closure);

        $decoded = json_decode($packed, true);
        $this->assertSame('closure', $decoded['type']);
        $this->assertNotNull($decoded['value']['useContext']);
        $this->assertStringContainsString('captured value', $decoded['value']['useContext']);
    }

    public function test_packs_static_closure(): void
    {
        $packer = new Packer();
        $closure = static function () {
            return 'static';
        };

        $packed = $packer->pack($closure);

        $decoded = json_decode($packed, true);
        $this->assertSame('closure', $decoded['type']);
        $this->assertTrue($decoded['value']['static']);
    }

    public function test_packs_recursive_closure(): void
    {
        $packer = new Packer();
        $factorial = static function (int $n) use (&$factorial): int {
            return $n <= 1 ? 1 : $n * $factorial($n - 1);
        };

        $packed = $packer->pack($factorial);

        $decoded = json_decode($packed, true);
        $this->assertSame('closure', $decoded['type']);
        $this->assertNotNull($decoded['value']['useContext']);
        $this->assertStringContainsString('@closureReference', $decoded['value']['useContext']);
    }

    public function test_packs_closure_with_object_binding(): void
    {
        $packer = new Packer();
        $obj = new TestCustomObject('bound_test', 99);
        $closure = function () {
            return $this->publicName;
        };
        $boundClosure = \Closure::bind($closure, $obj, TestCustomObject::class);

        $packed = $packer->pack($boundClosure);

        $decoded = json_decode($packed, true);
        $this->assertSame('closure', $decoded['type']);
        $this->assertNotNull($decoded['value']['closureThis']);
        $this->assertStringContainsString('bound_test', $decoded['value']['closureThis']);
    }

    public function test_unpacks_closure_and_executes(): void
    {
        $packer = new Packer();
        $closure = static function () {
            return 42;
        };

        $packed = $packer->pack($closure);
        $unpacked = $packer->unpack($packed);

        $this->assertIsCallable($unpacked);
        $this->assertSame(42, $unpacked());
    }

    public function test_packs_closure_as_null_when_nullify_enabled(): void
    {
        $packer = new Packer(nullifyClosures: true);
        $closure = static function () {
            return 42;
        };

        $packed = $packer->pack($closure);

        $decoded = json_decode($packed, true);
        $this->assertSame(['type' => 'closure', 'value' => null], $decoded);
    }

    public function test_unpacks_nullified_closure_as_null(): void
    {
        $packer = new Packer(nullifyClosures: true);
        $closure = static function () {
            return 42;
        };

        $packed = $packer->pack($closure);
        $unpacked = $packer->unpack($packed);

        $this->assertNull($unpacked);
    }

    public function test_roundtrip_simple_closure_executes(): void
    {
        $packer = new Packer();
        $closure = static fn() => 42;

        $packed = $packer->pack($closure);
        $unpacked = $packer->unpack($packed);

        $this->assertIsCallable($unpacked);
        $this->assertSame(42, $unpacked());
    }

    public function test_roundtrip_closure_with_use_context(): void
    {
        $packer = new Packer();
        $value = 'preserved';
        $closure = static function () use ($value) {
            return $value;
        };

        $packed = $packer->pack($closure);
        $unpacked = $packer->unpack($packed);

        $this->assertIsCallable($unpacked);
        $this->assertSame('preserved', $unpacked());
    }

    public function test_roundtrip_recursive_closure(): void
    {
        $packer = new Packer();
        $factorial = static function (int $n) use (&$factorial): int {
            return $n <= 1 ? 1 : $n * $factorial($n - 1);
        };

        $packed = $packer->pack($factorial);
        $unpacked = $packer->unpack($packed);

        $this->assertIsCallable($unpacked);
        $this->assertSame(120, $unpacked(5));
    }

    public function test_packs_arrow_function(): void
    {
        $packer = new Packer();
        $arrow = static fn($x) => $x * 2;

        $packed = $packer->pack($arrow);

        $decoded = json_decode($packed, true);
        $this->assertSame('closure', $decoded['type']);
        $this->assertStringContainsString('$x', $decoded['value']['code']);
    }

    public function test_packs_closure_with_return_type(): void
    {
        $packer = new Packer();
        $closure = static function (): int {
            return 42;
        };

        $packed = $packer->pack($closure);

        $decoded = json_decode($packed, true);
        $this->assertSame('closure', $decoded['type']);
        $this->assertStringContainsString(': int', $decoded['value']['code']);
    }

    public function test_packs_closure_with_default_parameter_values(): void
    {
        $packer = new Packer();
        $closure = static function ($a = 1, $b = 'test') {
            return [$a, $b];
        };

        $packed = $packer->pack($closure);

        $decoded = json_decode($packed, true);
        $this->assertSame('closure', $decoded['type']);
        $this->assertStringContainsString('$a = 1', $decoded['value']['code']);
        $this->assertStringContainsString("'test'", $decoded['value']['code']);
    }

    public function test_unpack_integer(): void
    {
        $packer = new Packer();
        $original = 42;

        $packed = $packer->pack($original);
        $unpacked = $packer->unpack($packed);

        $this->assertSame($original, $unpacked);
    }

    public function test_unpack_float(): void
    {
        $packer = new Packer();
        $original = 3.14;

        $packed = $packer->pack($original);
        $unpacked = $packer->unpack($packed);

        $this->assertSame($original, $unpacked);
    }

    public function test_unpack_string(): void
    {
        $packer = new Packer();
        $original = 'hello world';

        $packed = $packer->pack($original);
        $unpacked = $packer->unpack($packed);

        $this->assertSame($original, $unpacked);
    }

    public function test_unpack_boolean(): void
    {
        $packer = new Packer();

        $packedTrue = $packer->pack(true);
        $packedFalse = $packer->pack(false);

        $this->assertTrue($packer->unpack($packedTrue));
        $this->assertFalse($packer->unpack($packedFalse));
    }

    public function test_unpack_null(): void
    {
        $packer = new Packer();

        $packed = $packer->pack(null);
        $unpacked = $packer->unpack($packed);

        $this->assertNull($unpacked);
    }

    public function test_unpack_array(): void
    {
        $packer = new Packer();
        $original = ['a' => 1, 'b' => [2, 3], 'c' => 'string'];

        $packed = $packer->pack($original);
        $unpacked = $packer->unpack($packed);

        $this->assertSame($original, $unpacked);
    }

    public function test_unpack_object(): void
    {
        $packer = new Packer();
        $original = new TestCustomObject('test', 42);

        $packed = $packer->pack($original);
        $unpacked = $packer->unpack($packed);

        $this->assertInstanceOf(TestCustomObject::class, $unpacked);
        $this->assertSame('test', $unpacked->publicName);
        $this->assertSame(42, $unpacked->getPrivateValue());
    }

    public function test_unpack_circular_reference(): void
    {
        $packer = new Packer();
        $obj = new stdClass();
        $obj->name = 'parent';
        $obj->self = $obj;

        $packed = $packer->pack($obj);
        $unpacked = $packer->unpack($packed);

        $this->assertInstanceOf(stdClass::class, $unpacked);
        $this->assertSame('parent', $unpacked->name);
        $this->assertSame($unpacked, $unpacked->self);
    }

    public function test_unpack_exception(): void
    {
        $packer = new Packer();
        $original = new Exception('Test error', 123);

        $packed = $packer->pack($original);
        $unpacked = $packer->unpack($packed);

        $this->assertInstanceOf(Exception::class, $unpacked);
        $this->assertSame('Test error', $unpacked->getMessage());
        $this->assertSame(123, $unpacked->getCode());
    }

    public function test_unpack_throws_on_invalid_json(): void
    {
        $packer = new Packer();

        $this->expectException(PackerException::class);
        $this->expectExceptionMessage('Failed to decode value');

        $packer->unpack('not valid json');
    }

    public function test_unpack_throws_on_missing_type_field(): void
    {
        $packer = new Packer();

        $this->expectException(PackerException::class);
        $this->expectExceptionMessage('missing or invalid type field');

        $packer->unpack('{"value": 42}');
    }

    public function test_unpack_throws_on_unknown_type(): void
    {
        $packer = new Packer();

        $this->expectException(PackerException::class);
        $this->expectExceptionMessage('Unknown type: foo');

        $packer->unpack('{"type": "foo", "value": 42}');
    }

    public function test_unpack_throws_on_invalid_reference(): void
    {
        $packer = new Packer();

        $this->expectException(PackerException::class);
        $this->expectExceptionMessage('Invalid reference: @ref_99');

        $packer->unpack('{"type": "reference", "value": "@ref_99"}');
    }

    public function test_unpack_returns_incomplete_class_proxy_when_class_not_found(): void
    {
        // Mirror PHP's native serialize/unserialize behavior: a missing class becomes
        // a __PHP_Incomplete_Class proxy that preserves the class name and properties
        // for inspection, instead of a hard PackerException. The motivating scenario
        // is PHPUnit-generated Mock_<Class>_<hex> classes that exist in the test
        // process but not in worker / fork processes — when those mocks are reachable
        // through the closure use-context but unused by the body, the unpack must
        // proceed instead of aborting the entire run.
        $packer = new Packer();

        $unpacked = $packer->unpack(
            '{"type":"object","value":{"@class":"NonExistent","@ref":"@ref_0",'
            . '"name":{"type":"string","value":"alice"}}}'
        );

        $this->assertInstanceOf(\__PHP_Incomplete_Class::class, $unpacked);
        $vars = (array) $unpacked;
        $this->assertSame('NonExistent', $vars['__PHP_Incomplete_Class_Name']);
        $this->assertSame('alice', $vars['name']);
    }

    public function test_unpack_resolves_internal_reference_to_incomplete_class_proxy(): void
    {
        // Cross-graph reference resolution still works when the target object is an
        // incomplete-class proxy: a sibling property pointing at the same @ref must
        // unpack as the SAME instance, not throw or short-circuit to null.
        $packer = new Packer();

        $unpacked = $packer->unpack(
            '{"type":"array","value":{'
            . '"a":{"type":"object","value":{"@class":"NonExistent","@ref":"@ref_0"}},'
            . '"b":{"type":"reference","value":"@ref_0"}'
            . '}}'
        );

        $this->assertIsArray($unpacked);
        $this->assertInstanceOf(\__PHP_Incomplete_Class::class, $unpacked['a']);
        $this->assertSame($unpacked['a'], $unpacked['b']);
    }

    public function test_packs_empty_array(): void
    {
        $packer = new Packer();

        $packed = $packer->pack([]);

        $decoded = json_decode($packed, true);
        $this->assertSame(['type' => 'array', 'value' => []], $decoded);
    }

    public function test_packs_deeply_nested_structure(): void
    {
        $packer = new Packer();
        $nested = ['level1' => ['level2' => ['level3' => ['level4' => ['level5' =>
            ['level6' => ['level7' => ['level8' => ['level9' => ['level10' => 'deep']]]]]]]]]];

        $packed = $packer->pack($nested);
        $unpacked = $packer->unpack($packed);

        $this->assertSame($nested, $unpacked);
    }

    public function test_packs_unicode_strings(): void
    {
        $packer = new Packer();
        $unicode = "Hello \u{1F600} \u{4E2D}\u{6587} \u{0627}\u{0644}\u{0639}\u{0631}\u{0628}\u{064A}\u{0629}";

        $packed = $packer->pack($unicode);
        $unpacked = $packer->unpack($packed);

        $this->assertSame($unicode, $unpacked);
    }

    public function test_packs_binary_strings(): void
    {
        $packer = new Packer();
        $binary = "binary\x00with\x01control\x02chars";

        $packed = $packer->pack($binary);
        $unpacked = $packer->unpack($packed);

        $this->assertSame($binary, $unpacked);
    }

    public function test_packs_empty_object(): void
    {
        $packer = new Packer();
        $obj = new stdClass();

        $packed = $packer->pack($obj);
        $unpacked = $packer->unpack($packed);

        $this->assertInstanceOf(stdClass::class, $unpacked);
        $this->assertEmpty(get_object_vars($unpacked));
    }

    public function test_packs_exception_with_previous(): void
    {
        $packer = new Packer();
        $previous = new Exception('Previous error', 1);
        $exception = new Exception('Main error', 2, $previous);

        $packed = $packer->pack($exception);
        $decoded = json_decode($packed, true);

        $this->assertSame('object', $decoded['type']);
        $this->assertArrayHasKey('previous', $decoded['value']);
        $this->assertSame('object', $decoded['value']['previous']['type']);
    }

    public function test_packs_nan_float(): void
    {
        $packer = new Packer();

        $packed = $packer->pack(NAN);

        $decoded = json_decode($packed, true);
        $this->assertSame('float', $decoded['type']);
        $this->assertSame('NAN', $decoded['value']);
    }

    public function test_packs_inf_float(): void
    {
        $packer = new Packer();

        $packedInf = $packer->pack(INF);
        $packedNegInf = $packer->pack(-INF);

        $decodedInf = json_decode($packedInf, true);
        $decodedNegInf = json_decode($packedNegInf, true);
        $this->assertSame('float', $decodedInf['type']);
        $this->assertSame('INF', $decodedInf['value']);
        $this->assertSame('float', $decodedNegInf['type']);
        $this->assertSame('-INF', $decodedNegInf['value']);
    }

    public function test_packs_large_integer_beyond_js_safe(): void
    {
        $packer = new Packer();
        $largeInt = PHP_INT_MAX;

        $packed = $packer->pack($largeInt);

        $decoded = json_decode($packed, true);
        $this->assertSame('integer', $decoded['type']);
        $this->assertSame((string) $largeInt, $decoded['value']);
    }

    public function test_unpack_nan_float(): void
    {
        $packer = new Packer();

        $packed = $packer->pack(NAN);
        $unpacked = $packer->unpack($packed);

        $this->assertTrue(is_nan($unpacked));
    }

    public function test_unpack_inf_float(): void
    {
        $packer = new Packer();

        $packedInf = $packer->pack(INF);
        $packedNegInf = $packer->pack(-INF);

        $this->assertSame(INF, $packer->unpack($packedInf));
        $this->assertSame(-INF, $packer->unpack($packedNegInf));
    }

    public function test_unpack_keeps_wrapper_registered_after_call(): void
    {
        // The wrapper must stay registered after unpack(): downstream code (PHPUnit's
        // Util\Filter::isFiltered, MonkeyPatch FileStreamWrapper::url_stat, exception
        // pretty-printers) walks stack frames and calls is_file() / file_exists() on
        // every entry. When a frame's `file` is `closure://...` and the wrapper has
        // been unregistered, those filesystem probes emit
        // `is_file(): Unable to find the wrapper "closure"` warnings that worker error
        // handlers convert to fatal ErrorExceptions.
        $packer = new Packer();
        $packer->unpack('{"type":"null","value":null}');

        $this->assertContains('closure', stream_get_wrappers());
    }

    public function test_unpack_keeps_wrapper_registered_after_exception(): void
    {
        $packer = new Packer();

        try {
            $packer->unpack('{"type":"foo","value":1}');
            $this->fail('Expected PackerException for unknown type.');
        } catch (PackerException) {
            // Expected.
        }

        $this->assertContains('closure', stream_get_wrappers());
    }

    public function test_unpacks_closure_using_short_class_name_via_use_statement(): void
    {
        $packer = new Packer();
        $closure = static function (): object {
            return new Packer();
        };

        $unpacked = $packer->unpack($packer->pack($closure));

        $this->assertInstanceOf(Packer::class, $unpacked());
    }

    public function test_unpacks_closure_using_aliased_use_statement(): void
    {
        $packer = new Packer();
        $closure = static function (): object {
            return new FsAlias();
        };

        $unpacked = $packer->unpack($packer->pack($closure));

        $this->assertInstanceOf(Filesystem::class, $unpacked());
    }

    public function test_unpacks_closure_using_qualified_class_name_unchanged(): void
    {
        $packer = new Packer();
        $closure = static function (): object {
            return new \stdClass();
        };

        $unpacked = $packer->unpack($packer->pack($closure));

        $this->assertInstanceOf(stdClass::class, $unpacked());
    }

    public function test_unpacks_closure_using_short_class_name_as_typehint(): void
    {
        $packer = new Packer();
        $closure = static function (Packer $p): bool {
            return $p instanceof Packer;
        };

        $unpacked = $packer->unpack($packer->pack($closure));

        $this->assertTrue($unpacked(new Packer()));
    }

    public function test_unpacks_closure_using_short_class_name_as_return_type(): void
    {
        $packer = new Packer();
        $closure = static function (): Packer {
            return new Packer();
        };

        $unpacked = $packer->unpack($packer->pack($closure));

        $this->assertInstanceOf(Packer::class, $unpacked());
    }

    public function test_unpacks_closure_using_short_class_name_in_catch(): void
    {
        $packer = new Packer();
        $closure = static function (): string {
            try {
                throw new \RuntimeException('boom');
            } catch (Exception $e) {
                return $e->getMessage();
            }
        };

        $unpacked = $packer->unpack($packer->pack($closure));

        $this->assertSame('boom', $unpacked());
    }

    public function test_unpacks_closure_using_short_class_name_in_instanceof(): void
    {
        $packer = new Packer();
        $closure = static function (object $value): bool {
            return $value instanceof Packer;
        };

        $unpacked = $packer->unpack($packer->pack($closure));

        $this->assertTrue($unpacked(new Packer()));
        $this->assertFalse($unpacked(new stdClass()));
    }

    public function test_unpacks_closure_with___FILE___magic_constant(): void
    {
        $packer = new Packer();
        $closure = static function (): string {
            return __FILE__;
        };

        $unpacked = $packer->unpack($packer->pack($closure));

        $this->assertSame(__FILE__, $unpacked());
    }

    public function test_unpacks_closure_with___DIR___magic_constant(): void
    {
        $packer = new Packer();
        $closure = static function (): string {
            return __DIR__;
        };

        $unpacked = $packer->unpack($packer->pack($closure));

        $this->assertSame(__DIR__, $unpacked());
    }

    public function test_unpacks_closure_with___LINE___magic_constant(): void
    {
        $packer = new Packer();
        $expectedLine = __LINE__ + 2;
        $closure = static function (): int {
            return __LINE__;
        };

        $unpacked = $packer->unpack($packer->pack($closure));

        $this->assertSame($expectedLine, $unpacked());
    }

    public function test_unpacks_closure_with___NAMESPACE___magic_constant(): void
    {
        $packer = new Packer();
        $closure = static function (): string {
            return __NAMESPACE__;
        };

        $unpacked = $packer->unpack($packer->pack($closure));

        $this->assertSame(__NAMESPACE__, $unpacked());
    }

    public function test_unpacks_closure_with_string_literal_containing___DIR___unchanged(): void
    {
        $packer = new Packer();
        $closure = static function (): string {
            return 'literal __DIR__ stays';
        };

        $unpacked = $packer->unpack($packer->pack($closure));

        $this->assertSame('literal __DIR__ stays', $unpacked());
    }

    public function test_object_pack_skips_static_properties(): void
    {
        $heavy = new stdClass();
        $heavy->big = str_repeat('x', 1024);
        PackerStaticPropertyHost::$sharedRegistry = ['heavy' => $heavy];
        PackerStaticPropertyHost::$globalCounter = 99;

        try {
            $host = new PackerStaticPropertyHost();
            $packer = new Packer();

            $packed = $packer->pack($host);

            $decoded = json_decode($packed, true);
            $this->assertSame('object', $decoded['type']);
            $this->assertArrayNotHasKey('sharedRegistry', $decoded['value']);
            $this->assertArrayNotHasKey('globalCounter', $decoded['value']);
            $this->assertSame('host', $decoded['value']['instanceName']['value']);
            $this->assertSame(7, $decoded['value']['instanceValue']['value']);
        } finally {
            PackerStaticPropertyHost::$sharedRegistry = [];
            PackerStaticPropertyHost::$globalCounter = 0;
        }
    }

    public function test_unpacking_object_does_not_overwrite_static_properties(): void
    {
        $live = new stdClass();
        PackerStaticPropertyHost::$sharedRegistry = ['live' => $live];
        PackerStaticPropertyHost::$globalCounter = 42;

        try {
            $packed = json_encode([
                'type' => 'object',
                'value' => [
                    '@class' => PackerStaticPropertyHost::class,
                    '@ref' => '@ref_0',
                    'sharedRegistry' => ['type' => 'array', 'value' => []],
                    'globalCounter' => ['type' => 'integer', 'value' => 0],
                    'instanceName' => ['type' => 'string', 'value' => 'unpacked'],
                    'instanceValue' => ['type' => 'integer', 'value' => 11],
                ],
            ]);

            $packer = new Packer();
            $unpacked = $packer->unpack($packed);

            $this->assertInstanceOf(PackerStaticPropertyHost::class, $unpacked);
            $this->assertSame('unpacked', $unpacked->instanceName);
            $this->assertSame(11, $unpacked->instanceValue);
            $this->assertSame(['live' => $live], PackerStaticPropertyHost::$sharedRegistry);
            $this->assertSame(42, PackerStaticPropertyHost::$globalCounter);
        } finally {
            PackerStaticPropertyHost::$sharedRegistry = [];
            PackerStaticPropertyHost::$globalCounter = 0;
        }
    }
}

class TestCustomObject
{
    public string $publicName;
    private int $privateValue;

    public function __construct(string $name, int $value)
    {
        $this->publicName = $name;
        $this->privateValue = $value;
    }

    public function getPrivateValue(): int
    {
        return $this->privateValue;
    }
}

class PackerStaticPropertyHost
{
    /**
     * @var array<string,object>
     */
    public static array $sharedRegistry = [];
    public static int $globalCounter = 0;
    public string $instanceName = 'host';
    public int $instanceValue = 7;
}

