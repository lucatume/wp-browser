<?php

namespace lucatume\WPBrowser;

class envTest extends \Codeception\Test\Unit
{
    /**
     * It should allow loading an env file
     *
     * @test
     */
    public function should_allow_loading_an_env_file()
    {
        $envFile = codecept_data_dir('envFiles/testEnvFile1.env');

        loadEnvMap(envFile($envFile));

        $this->assertEquals(23, getenv('TEST_ENV_VAR_INT'));
        $this->assertEquals(23.89, getenv('TEST_ENV_VAR_FLOAT'));
        $this->assertEquals('lorem', getenv('TEST_ENV_VAR_SINGLE_STRING'));
        $this->assertEquals('lorem dolor sit', getenv('TEST_ENV_VAR_SINGLE_MULTI_STRING'));
        $this->assertEquals('lorem dolor sit', getenv('TEST_ENV_VAR_SINGLE_MULTI_STRING_W_QUOTES'));
    }

    /**
     * It should correctly parse entries that have equal sign in content
     *
     * @test
     */
    public function should_correctly_parse_entries_that_have_equal_sign_in_content()
    {
        $envFile = codecept_data_dir('envFiles/testEnvFile3.env');

        loadEnvMap(envFile($envFile));

        $this->assertEquals(
            'mysql:host=192.168.10.10;dbname=wordpress_test',
            getenv('TEST_ENV_3_VAR_1')
        );
        $this->assertEquals(
            'mysql:host=192.168.10.10;dbname=wordpress_test',
            getenv('TEST_ENV_3_VAR_2')
        );
    }

    /**
     * It should handle comments correctly
     *
     * @test
     */
    public function should_handle_comments_correctly()
    {
        $envFile = codecept_data_dir('envFiles/testEnvFile4.env');

        loadEnvMap(envFile($envFile));

        $map = [
            'TEST_COMMENT_ENV_VAR_1'  => '23',
            'TEST_COMMENT_ENV_VAR_2'  => '89',
            'TEST_COMMENT_ENV_VAR_4'  => 'test-inline-comment',
            'TEST_COMMENT_ENV_VAR_5'  => 'test-inline-comment',
            'TEST_COMMENT_ENV_VAR_6'  => 'hash-containing-=-and-#-symbols',
            'TEST_COMMENT_ENV_VAR_7'  => 'hash-containing-=',
            'TEST_COMMENT_ENV_VAR_8'  => 'hash-containing-#',
            'TEST_COMMENT_ENV_VAR_9'  => 'hash-containing-"',
            'TEST_COMMENT_ENV_VAR_10' => 'hash-containing-"-and=#',
            'TEST_COMMENT_ENV_VAR_11' => 'hash-containing-"-and=#',
            'TEST_COMMENT_ENV_VAR_12' => 'hash-containing-"-and=#'
        ];
        foreach ($map as $key => $expected) {
            $this->assertEquals($expected, getenv($key));
        }
    }

    /**
     * It should allow loading an env file w/o overriding previous values
     *
     * @test
     */
    public function should_allow_loading_an_env_file_w_o_overriding_previous_values()
    {
        $envFile = codecept_data_dir('envFiles/testEnvFile4.env');

        loadEnvMap(envFile($envFile));

        $map = [
            'TEST_COMMENT_ENV_VAR_1'  => '23',
            'TEST_COMMENT_ENV_VAR_2'  => '89',
            'TEST_COMMENT_ENV_VAR_4'  => 'test-inline-comment',
            'TEST_COMMENT_ENV_VAR_5'  => 'test-inline-comment',
            'TEST_COMMENT_ENV_VAR_6'  => 'hash-containing-=-and-#-symbols',
            'TEST_COMMENT_ENV_VAR_7'  => 'hash-containing-=',
            'TEST_COMMENT_ENV_VAR_8'  => 'hash-containing-#',
            'TEST_COMMENT_ENV_VAR_9'  => 'hash-containing-"',
            'TEST_COMMENT_ENV_VAR_10' => 'hash-containing-"-and=#',
            'TEST_COMMENT_ENV_VAR_11' => 'hash-containing-"-and=#',
            'TEST_COMMENT_ENV_VAR_12' => 'hash-containing-"-and=#'
        ];
        foreach ($map as $key => $expected) {
            $this->assertEquals($expected, getenv($key));
        }
    }

    /**
     * It should allow loading multiple env files overriding or not existing values
     *
     * @test
     */
    public function should_allow_loading_multiple_env_files_overriding_or_not_existing_values()
    {
        $envFile1 = codecept_data_dir('envFiles/testEnvFile5.env');
        $envFile2 = codecept_data_dir('envFiles/testEnvFile6.env');
        $envFile3 = codecept_data_dir('envFiles/testEnvFile7.env');

        loadEnvMap(envFile($envFile1), true);
        loadEnvMap(envFile($envFile2), true);
        loadEnvMap(envFile($envFile3), false);

        $this->assertEquals('89', getenv('TEST_ENV_VAR'));
        $this->assertEquals('lorem', getenv('TEST_ENV_VAR_2'));
    }

    /**
     * It should handle empty fields correctly
     *
     * @test
     */
    public function should_handle_empty_fields_correctly()
    {
        $envFile = codecept_data_dir('envFiles/testEnvFile8.env');

        loadEnvMap(envFile($envFile), true);

        $this->assertEquals('', getenv('TEST_EMPTY_FIELD_W_DOUBLE_QUOTES'));
        $this->assertEquals('', getenv('TEST_EMPTY_FIELD_W_SINGLE_QUOTES'));
        $this->assertEquals('', getenv('TEST_EMPTY_FIELD_WO_QUOTES'));
    }
}
