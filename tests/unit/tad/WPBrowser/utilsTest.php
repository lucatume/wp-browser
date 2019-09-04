<?php namespace tad\WPBrowser;

class utilsTest extends \Codeception\Test\Unit
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
            ]
        ];
    }

    /**
     * Test slugify
     * @dataProvider slugifyDataProvider
     */
    public function test_slugify($input, $expected, $sep = null)
    {
        $this->assertEquals($expected, slug(...array_slice(func_get_args(), 1)));
    }

    public function slugifyDataProvider()
    {
        return [
            'empty_string'            => [ '', '' ],
            'one_word'                => [ 'test', 'test' ],
            'camelcase_str'           => [ 'testStringIsSlugified', 'test-string-is-slugified' ],
            'camelcase_str_w_numbers' => [ 'testString2IsSlugified', 'test-string-2-is-slugified' ],
            'snake_case_str'          => [ 'test_string_is_slugified', 'test-string-is-slugified' ],
            'snake_case_str_w_number' => [ 'test_string_2_is_slugified', 'test-string-2-is-slugified' ],
            'words'                   => [ 'Lorem dolor sit', 'lorem-dolor-sit' ],
            'words_and_numbers'       => [ 'Lorem dolor sit 23 et lorem 89', 'lorem-dolor-sit-23-et-lorem-89' ],
            '_empty_string'            => [ '', '','_' ],
            '_one_word'                => [ 'test', 'test','_' ],
            '_camelcase_str'           => [ 'testStringIsSlugified', 'test_string_is_slugified','_' ],
            '_camelcase_str_w_numbers' => [ 'testString2IsSlugified', 'test_string_2_is_slugified','_' ],
            '_snake_case_str'          => [ 'test_string_is_slugified', 'test_string_is_slugified','_' ],
            '_snake_case_str_w_number' => [ 'test_string_2_is_slugified', 'test_string_2_is_slugified','_' ],
            '_words'                   => [ 'Lorem dolor sit', 'lorem_dolor_sit','_' ],
            '_words_and_numbers'       => [ 'Lorem dolor sit 23 et lorem 89', 'lorem_dolor_sit_23_et_lorem_89','_' ],
        ];
    }
}
