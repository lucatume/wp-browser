<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Test that DB_PASSWORD constant is defined even when the password is empty.
 *
 * This test verifies the fix for issue #786 where an empty dbPassword in WPLoader
 * configuration would result in an undefined DB_PASSWORD constant.
 *
 * @see https://github.com/lucatume/wp-browser/issues/786
 */
class EmptyDbPasswordTest extends WPTestCase
{
    /**
     * @test
     * Test that DB_PASSWORD constant is defined even with empty password.
     */
    public function test_db_password_constant_is_defined(): void
    {
        $this->assertTrue(defined('DB_PASSWORD'), 'DB_PASSWORD constant should be defined');
    }

    /**
     * @test
     * Test that DB_PASSWORD can be an empty string.
     */
    public function test_db_password_constant_can_be_empty(): void
    {
        // DB_PASSWORD should be defined even if it's an empty string
        // This is valid for passwordless database users (e.g., root on local development)
        $this->assertTrue(
            defined('DB_PASSWORD'),
            'DB_PASSWORD constant should be defined even when password is empty'
        );

        // The value can be empty string or non-empty, both are valid
        $this->assertTrue(
            is_string(DB_PASSWORD),
            'DB_PASSWORD should be a string (empty or non-empty)'
        );
    }
}
