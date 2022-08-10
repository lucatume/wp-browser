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

    public function urlDomainDataSet(): array
    {
        return [
            'empty'  => ['',''],
            'standard'   => ['http://example.com','example.com'],
            'w_port'     => ['http://example.com:2389','example.com:2389'],
            'localhost'  => ['http://localhost','localhost'],
            'localhost_w_port'   => ['http://localhost:2389','localhost:2389'],
            'ip_address'     => ['http://1.2.3.4','1.2.3.4'],
            'ip_address_w_port'  => ['http://1.2.3.4:2389','1.2.3.4:2389'],
            'w_path'     => ['http://example.com/foo/bar','example.com/foo/bar'],
            'localhost_w_path'   => ['http://localhost/foo/bar','localhost/foo/bar'],
            'localhost_w_port_and_path'  => ['http://localhost:2389/foo/bar','localhost:2389/foo/bar'],
            'ipaddress_w_port_and_path'  => ['http://1.2.3.4:2389/foo/bar','1.2.3.4:2389/foo/bar'],
        ]   ;
    }
    /**
     * @dataProvider urlDomainDataSet
     */
    public function test_urlDomain($input, $expected): void
    {
        $this->assertEquals($expected, Url::getDomain($input))   ;
    }
}
