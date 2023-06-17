<?php namespace lucatume\WPBrowser\Polyfills\Dotenv;

use Codeception\Test\Unit;
use InvalidArgumentException;

class DotenvTest extends Unit
{
    /**
     * It should allow loading an env file
     *
     * @test
     */
    public function should_allow_loading_an_env_file()
    {
        $dotenv = new Dotenv(codecept_data_dir('envFiles'), 'testEnvFile2.env');
        $dotenv->load();

        $expected = [
            'TEST_ENV_2_VAR_INT'                          => 23,
            'TEST_ENV_2_VAR_FLOAT'                        => 23.89,
            'TEST_ENV_2_VAR_SINGLE_STRING'                => 'lorem',
            'TEST_ENV_2_VAR_SINGLE_MULTI_STRING'          => 'lorem dolor sit',
            'TEST_ENV_2_VAR_SINGLE_MULTI_STRING_W_QUOTES' => 'lorem dolor sit'
        ];

        foreach ($expected as $key => $value) {
            $this->assertEquals($value, getenv($key));
            $this->assertEquals($value, $_ENV[ $key ]);
            $this->assertEquals($value, $_SERVER[ $key ]);
        }
    }

    /**
     * It should throw if the root dir does not exist
     *
     * @test
     */
    public function should_throw_if_the_root_dir_does_not_exist()
    {
        $this->expectException(InvalidArgumentException::class);

        new Dotenv(codecept_data_dir('foo/bar'));
    }

    /**
     * It should throw if the env file does not exist
     *
     * @test
     */
    public function should_throw_if_the_env_file_does_not_exist()
    {
        $this->expectException(InvalidArgumentException::class);

        new Dotenv(codecept_data_dir('envFiles'), '.foo.bar');
    }
}
