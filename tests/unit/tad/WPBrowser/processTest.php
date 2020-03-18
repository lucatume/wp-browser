<?php namespace tad\WPBrowser;

class processTest extends \Codeception\Test\Unit
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
}
