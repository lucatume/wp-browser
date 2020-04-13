<?php namespace tad\WPBrowser;

class stringTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider buildCommandlineDataProvider
     */
    public function test_build_commandline($input, $expected)
    {
        $this->assertEquals($expected, buildCommandline($input));
    }

    public function buildCommandlineDataProvider()
    {
        return [
            'empty_string_input' => ['',[]],
            'array_input'              => [
                [ 'core', 'version', '--allow-root' ],
                [ 'core', 'version', '--allow-root' ]
            ],
            'one_word_string'          => [ 'core', [ 'core' ] ],
            'one_option_string'        => [ '--help', [ '--help' ] ],
            'string_w_flag'            => [ 'core version --allow-root', [ 'core', 'version', '--allow-root' ] ],
            'string_w_3_flags'         => [
                'core version --allow-root --flag_2 --flag_3',
                [ 'core', 'version', '--allow-root', '--flag_2','--flag_3' ]
            ],
            'string_w_unquoted_option' => [ 'core version --opt_1=foo', [ 'core', 'version', '--opt_1=foo' ] ],
            'string_w_quoted_option'   => [
                'core version --opt_1="foo bar"',
                [ 'core', 'version', '--opt_1=foo bar' ]
            ],
            'string_input_mix'           => [
                'some command --opt_1="/var/www/html" --flag_2 --opt_2="Happy \"Quotes\""',
                [ 'some', 'command', '--opt_1=/var/www/html', '--flag_2', '--opt_2=Happy \"Quotes\"' ]
            ],
            'case_1'=>[
            'post create --post_title="Some Post" --post_type=post' ,
               ['post','create','--post_title=Some Post','--post_type=post']
            ],
            'issue_310' => [
                "post create --format=json --porcelain --post_title='Post for ALC newsletter testing'",
                ['post', 'create', '--format=json', '--porcelain', '--post_title=Post for ALC newsletter testing']
            ]
        ];
    }

    /**
     * Test slugify
     * @dataProvider slugifyDataProvider
     */
    public function test_slugify($input, $expected, $sep = null, $let = null)
    {
        $this->assertEquals($expected, slug(...array_slice(func_get_args(), 1)));
    }

    public function slugifyDataProvider()
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
    public function test_render_string($template, $data, $fnArgs, $expected)
    {
        $this->assertEquals($expected, renderString($template, $data, $fnArgs));
    }

    public function renderStringDataProvider()
    {
        $session = static function ($session) {
            return 'xyz_' . $session;
        };

        return [
            'empty' => ['',[],[],''],
            'empty_w_data' => ['',['name'=>'luca'],[],''],
            'empty_w_data_and_seed' => ['',['name'=>'luca'],['session'=>'test'],''],
            'template_w_data_and_seed' => [
                '{{session}}_{{name}}',
                [ 'name' => 'luca', 'session' => $session ],
                [ 'session' => 'test' ],
                'xyz_test_luca'
            ],
            'handlebar_template' => [
            'render{{#if name}} with {{name}}{{/if}}'   ,
                [ 'name' => 'luca' ],
                [  ],
                'render with luca'
            ]
        ];
    }

    public function isRegexDataProvider()
    {
        return [
            'empty' => [ '', false ],
            'simple_string' => [ 'foo-bar', false ],
            'simple_regex' => [ '/foo-bar/', true ],
            'complex_regex' => [ '#^\\s{4}\\[\\d]#im', true ],
        ];
    }

    /**
     * Test isRegex
     *
     * @dataProvider isRegexDataProvider
     */
    public function test_is_regex($candidate, $expected)
    {
        $this->assertEquals($expected, isRegex($candidate));
    }

    public function andListDataProvider()
    {
        return [
        'empty' => [[],''],
        'one_el' => [['foo'],'foo'],
        'two_els' => [['foo','bar'],'foo and bar'],
        'three_els' => [['foo','bar','baz'],'foo, bar and baz'],
        'four_els' => [['foo','bar','baz','woot'],'foo, bar, baz and woot'],
        ];
    }
    /**
     * Test andList
     * @dataProvider andListDataProvider
     */
    public function test_and_list($input, $expected)
    {
        $this->assertEquals($expected, andList($input));
    }
}
