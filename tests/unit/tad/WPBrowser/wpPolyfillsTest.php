<?php namespace lucatume\WPBrowser;

class wpPolyfillsTest extends \Codeception\Test\Unit
{

    public function sanitizeUserDataSet()
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
     * @dataProvider sanitizeUserDataSet
     */
    public function test_sanitizeUser($input, $expected)
    {
        $this->assertEquals($expected, sanitize_user($input));
    }
}
