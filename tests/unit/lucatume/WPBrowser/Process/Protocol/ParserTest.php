<?php

namespace Unit\lucatume\WPBrowser\Process\Protocol;

use Codeception\Test\Unit;
use Generator;
use InvalidArgumentException;
use lucatume\WPBrowser\Process\Protocol\Parser;
use lucatume\WPBrowser\Process\Protocol\ProtocolException;

class ParserTest extends Unit
{

    public function decodeBadInputDataProvider(): Generator
    {
        yield 'empty' => [
            '',
            ProtocolException::EMPTY_INPUT
        ];

        // Length is 16.
        $serializedStringFoo = base64_encode(serialize('foo'));
        yield 'missing start char' => [
            $serializedStringFoo,
            ProtocolException::MISSING_START_CHAR
        ];

        yield 'non-numeric length' => [
            "\$foo\r\n",
            ProtocolException::NON_NUMERIC_LENGTH
        ];

        yield 'mismatching length' => [
            "\$30\r\n" . $serializedStringFoo . "\r\n",
            ProtocolException::MISMATCHING_LENGTH
        ];

        yield 'missing ending CRLF' => [
            "\$16\r\n" . $serializedStringFoo,
            ProtocolException::MISSING_ENDING_CRLF
        ];

        // Length is 20.
        $serializedStringFoobar = base64_encode(serialize('foobar'));
        yield "mismatching length at second position" => [
            "\$16\r\n" . $serializedStringFoo . "\r\n30\r\n" . $serializedStringFoobar . "\r\n",
            ProtocolException::MISMATCHING_LENGTH
        ];

        yield 'missing ending CRLF at second position' => [
            "\$16\r\n" . $serializedStringFoo . "\r\n20\r\n" . $serializedStringFoobar,
            ProtocolException::MISSING_ENDING_CRLF
        ];

        yield 'content not base64 encoded' => [
            "\$7\r\nfoo~bar\r\n",
            ProtocolException::INCORRECT_ENCODING
        ];

        yield 'content not base64 encoded at second position' => [
            "\$16\r\n" . $serializedStringFoo . "\r\n7\r\nfoo~bar\r\n",
            ProtocolException::INCORRECT_ENCODING
        ];

        // foo => Zm9v
        yield 'content not serialized' => [
            "\$4\r\n" . base64_encode('foo') . "\r\n",
            ProtocolException::INCORRECT_ENCODING
        ];

        yield 'content not serialized at second position' => [
            "\$16\r\n" . $serializedStringFoo . "\r\n4\r\n" . base64_encode('foo') . "\r\n",
            ProtocolException::INCORRECT_ENCODING
        ];
    }

    /**
     * @dataProvider  decodeBadInputDataProvider
     */
    public function testDecodeBadInputs(string $input, int $expectedCode): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionCode($expectedCode);

        Parser::decode($input);
    }

    public function decodeDataProvider(): Generator
    {
        yield 'empty input' => [
            "\$0\r\n\r\n",
            ['']
        ];

        $encodedSerializedFoo = base64_encode(serialize('foo'));
        yield 'single input' => [
            "\$16\r\n" . $encodedSerializedFoo . "\r\n",
            ['foo']
        ];

        yield 'single input and empty string' => [
            "\$16\r\n" . $encodedSerializedFoo . "\r\n0\r\n\r\n",
            ['foo', '']
        ];

        yield 'input, empty string, input' => [
            "\$16\r\n" . $encodedSerializedFoo . "\r\n0\r\n\r\n16\r\n"
            . base64_encode(serialize('bar')) . "\r\n",
            ['foo', '', 'bar']
        ];

        yield '5 inputs' => [
            "\$16\r\n" . $encodedSerializedFoo . "\r\n0\r\n\r\n16\r\n"
            . base64_encode(serialize('bar')) . "\r\n16\r\n" . base64_encode(serialize('baz'))
            . "\r\n16\r\n" . base64_encode(serialize('qux')) . "\r\n16\r\n"
            . base64_encode(serialize('quux')) . "\r\n",
            ['foo', '', 'bar', 'baz', 'qux', 'quux']
        ];

        yield 'inputs and empty strings' => [
            "\$16\r\n" . $encodedSerializedFoo . "\r\n0\r\n\r\n16\r\n"
            . base64_encode(serialize('bar')) . "\r\n0\r\n\r\n16\r\n" . base64_encode(serialize('baz'))
            . "\r\n0\r\n\r\n16\r\n" . base64_encode(serialize('qux')) . "\r\n0\r\n\r\n16\r\n"
            . base64_encode(serialize('quux')) . "\r\n",
            ['foo', '', 'bar', '', 'baz', '', 'qux', '', 'quux']
        ];

        $encodedSerializedNumericArray = base64_encode(serialize(['foo', 'bar']));
        $encodedSerializedObject = base64_encode(serialize((object)['foo' => 'bar']));
        $encodedSerializedNumber = base64_encode(serialize(23));
        yield 'serialized arrays, objects, strings and numbers' => [
            "\$" . strlen($encodedSerializedNumericArray) . "\r\n" . $encodedSerializedNumericArray . "\r\n" .
            "0\r\n\r\n"
            . strlen($encodedSerializedObject) . "\r\n" . $encodedSerializedObject
            . "\r\n" . strlen($encodedSerializedFoo) . "\r\n" . $encodedSerializedFoo
            . "\r\n" . strlen($encodedSerializedNumber) . "\r\n" . $encodedSerializedNumber
            . "\r\n",
            [['foo', 'bar'], '', (object)['foo' => 'bar'], 'foo', 23]
        ];
    }

    /**
     * @dataProvider decodeDataProvider
     */
    public function testDecodeEncode(string $input, array $expected, int $offset = null, int $count = null): void
    {
        $parsed = Parser::decode($input, $offset, $count);
        $this->assertEquals($expected, $parsed);

        $this->assertEquals($input, Parser::encode($expected));
    }

    public function decodePartialDataProvider(): Generator
    {
        $encodedSerializedFoo = base64_encode(serialize('foo'));
        $encodedSerializedNumericArray = base64_encode(serialize(['foo', 'bar']));
        $encodedSerializedObject = base64_encode(serialize((object)['foo' => 'bar']));
        $encodedSerializedNumber = base64_encode(serialize(23));

        yield 'decode first chunk' => [
            "\$" . strlen($encodedSerializedNumericArray) . "\r\n" . $encodedSerializedNumericArray . "\r\n" .
            "0\r\n\r\n"
            . strlen($encodedSerializedObject) . "\r\n" . $encodedSerializedObject
            . "\r\n" . strlen($encodedSerializedFoo) . "\r\n" . $encodedSerializedFoo
            . "\r\n" . strlen($encodedSerializedNumber) . "\r\n" . $encodedSerializedNumber
            . "\r\n",
            0,
            1,
            [['foo', 'bar']]
        ];

        yield 'decode second chunk' => [
            "\$" . strlen($encodedSerializedNumericArray) . "\r\n" . $encodedSerializedNumericArray . "\r\n" .
            "0\r\n\r\n"
            . strlen($encodedSerializedObject) . "\r\n" . $encodedSerializedObject
            . "\r\n" . strlen($encodedSerializedFoo) . "\r\n" . $encodedSerializedFoo
            . "\r\n" . strlen($encodedSerializedNumber) . "\r\n" . $encodedSerializedNumber
            . "\r\n",
            1,
            1,
            ['']
        ];

        yield 'decode third and fourth chunks' => [
            "\$" . strlen($encodedSerializedNumericArray) . "\r\n" . $encodedSerializedNumericArray . "\r\n" .
            "0\r\n\r\n"
            . strlen($encodedSerializedObject) . "\r\n" . $encodedSerializedObject
            . "\r\n" . strlen($encodedSerializedFoo) . "\r\n" . $encodedSerializedFoo
            . "\r\n" . strlen($encodedSerializedNumber) . "\r\n" . $encodedSerializedNumber
            . "\r\n",
            2,
            2,
            [(object)['foo' => 'bar'], 'foo']
        ];

        yield 'decode with count higher than len' => [
            "\$" . strlen($encodedSerializedNumericArray) . "\r\n" . $encodedSerializedNumericArray . "\r\n" .
            "0\r\n\r\n"
            . strlen($encodedSerializedObject) . "\r\n" . $encodedSerializedObject
            . "\r\n" . strlen($encodedSerializedFoo) . "\r\n" . $encodedSerializedFoo
            . "\r\n" . strlen($encodedSerializedNumber) . "\r\n" . $encodedSerializedNumber
            . "\r\n",
            0,
            10,
            [['foo', 'bar'], '', (object)['foo' => 'bar'], 'foo', 23]
        ];
    }

    /**
     * @dataProvider decodePartialDataProvider
     */
    public function testDecodePartial(string $input, int $offset, int $count, mixed $expected): void
    {
        $parsed = Parser::decode($input, $offset, $count);
        $this->assertEquals($expected, $parsed);
    }

    public function testDecodeThrowsOnNegativeOffsets(): void
    {
        $this->expectException(ProtocolException::class);
        $this->expectExceptionCode(ProtocolException::DECODE_NEGATIVE_OFFSET);

        Parser::decode('foo', -1);
    }
}
