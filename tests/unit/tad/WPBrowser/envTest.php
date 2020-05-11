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
        $this->assertEquals('"lorem dolor sit"', getenv('TEST_ENV_VAR_SINGLE_MULTI_STRING_W_QUOTES'));
    }
}
