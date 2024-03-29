<?php


namespace Unit\lucatume\WPBrowser\Process\Protocol;

use Codeception\Test\Unit;
use Generator;
use lucatume\WPBrowser\Process\Protocol\Control;
use lucatume\WPBrowser\Process\Protocol\Parser;
use lucatume\WPBrowser\Process\Protocol\Request;
use lucatume\WPBrowser\Opis\Closure\SerializableClosure;

class RequestTest extends Unit
{

    public function getPayloadDataProvider(): Generator
    {
        yield 'empty control, empty function' => [
            [],
            new SerializableClosure(function () {
            })
        ];
        yield 'non-empty control, empty function' => [
            ['foo' => 'bar'],
            new SerializableClosure(function () {
            })
        ];
        yield 'empty control, non-empty function' => [
            [],
            new SerializableClosure(function () {
                return 'foo';
            })
        ];
        yield 'non-empty control, non-empty function' => [
            ['foo' => 'bar'],
            new SerializableClosure(function () {
                return 'foo';
            })
        ];
        yield 'empty control, empty static function' => [
            [],
            new SerializableClosure(static function () {
            })
        ];
        yield 'non-empty control, empty static function' => [
            ['foo' => 'bar'],
            new SerializableClosure(static function () {
            })
        ];
        yield 'empty control, non-empty static function' => [
            [],
            new SerializableClosure(static function () {
                return 'foo';
            })
        ];
        yield 'non-empty control, non-empty static function' => [
            ['foo' => 'bar'],
            new SerializableClosure(static function () {
                return 'foo';
            })
        ];
    }

    /**
     * @dataProvider getPayloadDataProvider
     */
    public function test_getPayload_fromPayload(array $control, SerializableClosure $serializableClosure): void
    {
        $encoded = Parser::encode([(new Control($control))->toArray(), $serializableClosure]);

        $request = new Request($control, $serializableClosure);

        $payload = $request->getPayload();
        $this->assertEquals($encoded, $payload);
        $fromPayload = Request::fromPayload($encoded);
        $this->assertEquals($request->getSerializableClosure(), $serializableClosure);
        $this->assertEquals($request->getControl(), $fromPayload->getControl());
    }

    /**
     * It should return a payload file path when getting payload on Windows
     *
     * @test
     */
    public function should_return_a_payload_file_path_when_getting_payload_on_windows(): void
    {
        $serializableClosure = new SerializableClosure(static function () {
            return 'foo';
        });
        $control = ['foo' => 'bar'];
        $encoded = Parser::encode([(new Control($control))->toArray(), $serializableClosure]);

        $request = new Request(['foo' => 'bar'], $serializableClosure);
        $request->setUseFilePayloads(true);
        $payload = $request->getPayload();

        $this->assertIsString($payload);
        $this->assertFileExists($payload);
        $this->assertStringEqualsFile($payload, $encoded);
    }
}
