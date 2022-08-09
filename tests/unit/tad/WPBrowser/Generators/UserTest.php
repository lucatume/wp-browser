<?php namespace lucatume\WPBrowser\Generators;

class UserTest extends \Codeception\Test\Unit
{
    public function usernamesDataSet()
    {
        return [
            'empty' => ['', ''],
            'slug_1' => ['foo', 'foo'],
            'slug_2' => ['foo_bar', 'foo_bar'],
            'slug_3' => ['foo-bar-baz', 'foo-bar-baz'],
            'camelCase' => ['MyUser', 'MyUser'],
            'spaced' => ['André Gueslin', 'Andre Gueslin'],
            'multi_spaced' => ['André     Gueslin', 'Andre Gueslin']
        ];
    }
    /**
     * It should correctly sanitize user names
     *
     * @test
     * @dataProvider usernamesDataSet
     */
    public function should_correctly_sanitize_user_names($login, $expected)
    {
        $userData = User::generateUserTableDataFrom($login);

        $this->assertEquals($expected, $userData['user_login']);
    }

    public function buildCapabilitiesDataSet()
    {
        yield 'empty' => ['',['wp_capabilities' => ['' => true]]];
        yield 'one_string_cap' => ['administrator', ['wp_capabilities' => ['administrator' => true]]];
        yield 'two_string_caps' => [
            ['administrator', 'editor'],
            ['wp_capabilities' => ['administrator' => true, 'editor' => true]]
        ];
        yield 'one_string_bool_cap' => [['author' => true], ['wp_capabilities' => ['author' => true]]];
        yield 'two_string_bool_caps' => [
            ['administrator' => true,'manage_options'=>false],
            ['wp_capabilities' => ['administrator' => true, 'manage_options'=>false]]
        ];
        yield 'one_blog_1_entry' => [
            [1 => 'editor'],
            ['wp_capabilities' => ['editor' => true]]
        ];
        yield 'one_blog_entry' => [
            [23 => 'editor'],
            ['wp_23_capabilities' => ['editor' => true]]
        ];
        yield 'two_blog_entries' => [
            [
                1=>['administrator'=>true,'manage_options'=>false],
                23 => ['editor','author']
            ],
            [
                'wp_capabilities' => ['administrator' => true, 'manage_options'=>false],
                'wp_23_capabilities' => ['editor' => true,'author'=>true]
            ]
        ];
        yield 'three_blog_entries' => [
            [
                1=>['administrator'=>true,'manage_options'=>false],
                23 => ['editor','author'],
                89 => ['edit_themes','subscriber']
            ],
            [
                'wp_capabilities' => ['administrator' => true, 'manage_options'=>false],
                'wp_23_capabilities' => ['editor' => true,'author'=>true],
                'wp_89_capabilities' => ['edit_themes' => true,'subscriber'=>true]
            ]
        ];
    }
    /**
     * It should correctly build caps
     *
     * @test
     * @dataProvider buildCapabilitiesDataSet
     */
    public function should_correctly_build_caps($input, $expected)
    {
        $roles = User::buildCapabilities($input, 'wp_');
        $this->assertEquals($expected, $roles);
    }
}
