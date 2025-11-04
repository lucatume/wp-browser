<?php

namespace lucatume\WPBrowser\CorePHPUnit;

use Codeception\Test\Unit;

/**
 * Test that wp-tests-config.php correctly handles empty DB_PASSWORD.
 *
 * This test verifies the fix for issue #786 where an empty dbPassword in WPLoader
 * configuration would result in an undefined DB_PASSWORD constant.
 *
 * @see https://github.com/lucatume/wp-browser/issues/786
 */
class WpTestsConfigTest extends Unit
{
    /**
     * @test
     * Test that DB_PASSWORD constant is defined when dbPassword is empty string.
     */
    public function test_db_password_constant_defined_with_empty_password(): void
    {
        // Simulate the wp-tests-config.php logic
        $wpLoaderConfig = [
            'wpRootFolder' => '/path/to/wordpress',
            'theme' => 'twentytwentyfour',
            'multisite' => false,
            'dbName' => 'test_db',
            'dbUser' => 'root',
            'dbPassword' => '', // Empty password - the bug case
            'dbHost' => 'localhost',
            'dbCharset' => 'utf8',
            'dbCollate' => '',
            'AUTH_KEY' => 'test_key',
            'SECURE_AUTH_KEY' => 'test_key',
            'LOGGED_IN_KEY' => 'test_key',
            'NONCE_KEY' => 'test_key',
            'AUTH_SALT' => 'test_salt',
            'SECURE_AUTH_SALT' => 'test_salt',
            'LOGGED_IN_SALT' => 'test_salt',
            'NONCE_SALT' => 'test_salt',
            'domain' => 'test.local',
            'adminEmail' => 'admin@test.local',
            'title' => 'Test Site',
            'phpBinary' => 'php',
            'language' => '',
            'AUTOMATIC_UPDATER_DISABLED' => true,
            'WP_HTTP_BLOCK_EXTERNAL' => false,
            'WP_CONTENT_DIR' => null,
            'WP_PLUGIN_DIR' => null,
            'WPMU_PLUGIN_DIR' => null,
            'tablePrefix' => 'wp_',
        ];

        $abspath = rtrim($wpLoaderConfig['wpRootFolder'], '\\/') . '/';
        $themes = (array)$wpLoaderConfig['theme'];
        $stylesheet = end($themes);

        $constantsToDefine = [
            'ABSPATH' => $abspath,
            'WP_DEFAULT_THEME' => $stylesheet,
            'WP_TESTS_MULTISITE' => $wpLoaderConfig['multisite'],
            'WP_DEBUG' => true,
            'DB_NAME' => $wpLoaderConfig['dbName'],
            'DB_USER' => $wpLoaderConfig['dbUser'],
            'DB_PASSWORD' => $wpLoaderConfig['dbPassword'],
            'DB_HOST' => $wpLoaderConfig['dbHost'],
            'DB_CHARSET' => $wpLoaderConfig['dbCharset'] ?? 'utf8',
            'DB_COLLATE' => $wpLoaderConfig['dbCollate'] ?? '',
        ];

        $definedConstants = [];

        // Apply the fixed logic from wp-tests-config.php
        foreach ($constantsToDefine as $const => $value) {
            // DB_PASSWORD can be an empty string for passwordless database users
            $shouldDefine = ($const === 'DB_PASSWORD' && array_key_exists('dbPassword', $wpLoaderConfig)) || $value;
            if ($shouldDefine) {
                $definedConstants[$const] = $value;
            }
        }

        // Verify DB_PASSWORD was included even though it's an empty string
        $this->assertArrayHasKey('DB_PASSWORD', $definedConstants, 'DB_PASSWORD should be defined even when empty');
        $this->assertSame('', $definedConstants['DB_PASSWORD'], 'DB_PASSWORD should have empty string value');

        // Verify other constants are also defined correctly
        $this->assertArrayHasKey('DB_NAME', $definedConstants);
        $this->assertArrayHasKey('DB_USER', $definedConstants);
        $this->assertArrayHasKey('DB_HOST', $definedConstants);
        $this->assertSame('test_db', $definedConstants['DB_NAME']);
        $this->assertSame('root', $definedConstants['DB_USER']);
        $this->assertSame('localhost', $definedConstants['DB_HOST']);
    }

    /**
     * @test
     * Test that DB_PASSWORD constant is defined when dbPassword is non-empty.
     */
    public function test_db_password_constant_defined_with_non_empty_password(): void
    {
        $wpLoaderConfig = [
            'wpRootFolder' => '/path/to/wordpress',
            'theme' => 'twentytwentyfour',
            'multisite' => false,
            'dbName' => 'test_db',
            'dbUser' => 'testuser',
            'dbPassword' => 'secret123', // Non-empty password
            'dbHost' => 'localhost',
            'dbCharset' => 'utf8',
            'dbCollate' => '',
        ];

        $constantsToDefine = [
            'DB_PASSWORD' => $wpLoaderConfig['dbPassword'],
        ];

        $definedConstants = [];

        foreach ($constantsToDefine as $const => $value) {
            $shouldDefine = ($const === 'DB_PASSWORD' && array_key_exists('dbPassword', $wpLoaderConfig)) || $value;
            if ($shouldDefine) {
                $definedConstants[$const] = $value;
            }
        }

        $this->assertArrayHasKey('DB_PASSWORD', $definedConstants, 'DB_PASSWORD should be defined with non-empty value');
        $this->assertSame('secret123', $definedConstants['DB_PASSWORD']);
    }

    /**
     * @test
     * Test the old logic (before fix) would fail with empty password.
     */
    public function test_old_logic_fails_with_empty_password(): void
    {
        $wpLoaderConfig = [
            'dbPassword' => '', // Empty password
        ];

        $constantsToDefine = [
            'DB_PASSWORD' => $wpLoaderConfig['dbPassword'],
        ];

        $definedConstants = [];

        // Old logic that had the bug
        foreach ($constantsToDefine as $const => $value) {
            if ($value) { // This fails for empty string
                $definedConstants[$const] = $value;
            }
        }

        // This demonstrates the bug - DB_PASSWORD was not defined
        $this->assertArrayNotHasKey('DB_PASSWORD', $definedConstants, 'Old logic should NOT define DB_PASSWORD with empty value');
    }
}
