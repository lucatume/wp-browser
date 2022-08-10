<?php

namespace lucatume\WPBrowser\Utils;

use Codeception\Test\Unit;

class StringsTest extends Unit
{
    public function andListDataProvider(): array
    {
        return [
            'empty' => [[], ''],
            'one_el' => [['foo'], 'foo'],
            'two_els' => [['foo', 'bar'], 'foo and bar'],
            'three_els' => [['foo', 'bar', 'baz'], 'foo, bar and baz'],
            'four_els' => [['foo', 'bar', 'baz', 'woot'], 'foo, bar, baz and woot'],
        ];
    }

    /**
     * Test andList
     * @dataProvider andListDataProvider
     */
    public function test_and_list($input, $expected): void
    {
        $this->assertEquals($expected, Strings::andList($input));
    }

    public function isRegexDataProvider(): array
    {
        return [
            'empty' => ['', false],
            'simple_string' => ['foo-bar', false],
            'simple_regex' => ['/foo-bar/', true],
            'complex_regex' => ['#^\\s{4}\\[\\d]#im', true],
        ];
    }

    /**
     * Test isRegex
     *
     * @dataProvider isRegexDataProvider
     */
    public function test_is_regex($candidate, $expected): void
    {
        $this->assertEquals($expected, Strings::isRegex($candidate));
    }
}
