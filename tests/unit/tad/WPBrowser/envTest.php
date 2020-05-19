<?php

namespace tad\WPBrowser;

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
}
