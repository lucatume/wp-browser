<?php namespace tad\WPBrowser\Generators;

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
}
