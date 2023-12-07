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

    /**
     * Test slugify
     * @dataProvider slugifyDataProvider
     */
    public function test_slugify($input, $expected, $sep = null, $let = null): void
    {
        $this->assertEquals($expected, Strings::slug(...array_slice(func_get_args(), 1)));
    }

    public function slugifyDataProvider(): array
    {
        return [
            'empty_string' => ['', '', '-', false],
            'one_word' => ['test', 'test', '-', false],
            'camelcase_str' => ['testStringIsSlugified', 'test-string-is-slugified', '-', false],
            'camelcase_str_w_numbers' => ['testString2IsSlugified', 'test-string-2-is-slugified', '-', false],
            'snake_case_str' => ['test_string_is_slugified', 'test-string-is-slugified', '-', false],
            'snake_case_str_w_number' => ['test_string_2_is_slugified', 'test-string-2-is-slugified', '-', false],
            'words' => ['Lorem dolor sit', 'lorem-dolor-sit', '-', false],
            'words_and_numbers' => ['Lorem dolor sit 23 et lorem 89', 'lorem-dolor-sit-23-et-lorem-89', '-', false],
            '_empty_string' => ['', '', '_', false],
            '_one_word' => ['test', 'test', '_', false],
            '_camelcase_str' => ['testStringIsSlugified', 'test_string_is_slugified', '_', false],
            '_camelcase_str_w_numbers' => ['testString2IsSlugified', 'test_string_2_is_slugified', '_', false],
            '_snake_case_str' => ['test_string_is_slugified', 'test_string_is_slugified', '_', false],
            '_snake_case_str_w_number' => ['test_string_2_is_slugified', 'test_string_2_is_slugified', '_', false],
            '_words' => ['Lorem dolor sit', 'lorem_dolor_sit', '_', false],
            '_words_and_numbers' => ['Lorem dolor sit 23 et lorem 89', 'lorem_dolor_sit_23_et_lorem_89', '_', false],
            'let_camelcase_str' => ['testStringIsSlugified', 'test-string-is-slugified', '-', true],
            'let_camelcase_str_w_numbers' => ['testString2IsSlugified', 'test-string-2-is-slugified', '-', true],
            'let_snake_case_str' => ['test_string_is_slugified', 'test_string_is_slugified', '-', true],
            'let_snake_case_str_2' => ['test_string_Is_Slugified', 'test_string_is_slugified', '-', true],
            'let_snake_case_str_3' => ['test_string_23_is_slugified', 'test_string_23_is_slugified', '-', true],
            'let_snake_case_str_w_number' => ['test_string_2_is_slugified', 'test_string_2_is_slugified', '-', true],
            '_let_camelcase_str' => ['testStringIsSlugified', 'test_string_is_slugified', '_', true],
            '_let_camelcase_str_w_numbers' => ['testString2IsSlugified', 'test_string_2_is_slugified', '_', true],
            '_let_snake_case_str' => ['test_string_is_slugified', 'test_string_is_slugified', '_', true],
            '_let_snake_case_str_w_number' => ['test_string_2_is_slugified', 'test_string_2_is_slugified', '_', true],
            '_let_hyphen_string' => ['test-string-is-slugified', 'test-string-is-slugified', '_', true],
            '_let_hyphen_string_w_number' => ['test-string-2-is-slugified', 'test-string-2-is-slugified', '_', true],
            'cat1' => ['cat1', 'cat1']
        ];
    }

    /**
     * Test renderString
     * @dataProvider renderStringDataProvider
     */
    public function test_render_string($template, $data, $fnArgs, $expected): void
    {
        $this->assertEquals($expected, Strings::renderString($template, $data, $fnArgs));
    }

    public function renderStringDataProvider(): array
    {
        return [
            'empty' => ['',[],[],''],
            'empty_w_data' => ['',['name'=>'luca'],[],''],
            'empty_w_data_and_seed' => ['',['name'=>'luca'],['session'=>'test'],''],
            'template_w_data_and_seed' => [
                '{{session}}_{{name}}',
                [ 'name' => 'luca', 'session' => static function ($session) {
                    return 'xyz_' . $session;
                }],
                [ 'session' => 'test' ],
                'xyz_test_luca'
            ]
        ];
    }
}
