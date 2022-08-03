<?php
namespace cli;

use ClimoduleTester;

use function putenv;

class EnvironmentCest
{
    
    /**
     * @test
     */
    public function that_environment_is_inherited_from_putenv(ClimoduleTester $I)
    {
        putenv('BAZ=BIZ');
    
        try {
            $I->cli(['eval','"var_dump(\$_SERVER[\'BAZ\'] ?? \'Not set\');"']);
            $I->seeInShellOutput('BIZ');
        } finally {
            putenv('BAZ');
        }

    }
    
    /**
     * @test
     */
    public function that_environment_is_inherited_from_ENV(ClimoduleTester $I)
    {
        $_ENV['FOO'] = 'BAR';
    
        try {
            $I->cli(['eval','"var_dump(\$_SERVER[\'FOO\'] ?? \'Not set\');"']);
            $I->seeInShellOutput('BAR');
        } finally {
            unset($_ENV['FOO']);
        }
    }
    
    /**
     * @test
     */
    public function inheriting_env_on_php_greater_than_71000(ClimoduleTester $I)
    {
        if(PHP_VERSION_ID <= 70100) {
            $I->markTestSkipped('This test should only run on PHP >= 7.1');
            return;
        }
    
        try {
            $_ENV['X_FOO'] = 'X_BAR';
            putenv('X_BAZ=X_BIZ');
    
            $I->cli(['eval','"var_dump(\$_SERVER[\'X_FOO\'] ?? \'Not set\');"']);
            $I->seeInShellOutput('X_BAR');
    
            $I->cli(['eval','"var_dump(\$_SERVER[\'X_BAZ\'] ?? \'Not set\');"']);
            $I->seeInShellOutput('X_BIZ');
    
            // putenv has a higher priority.
            putenv('X_FOO=X_BAR_putenv');
    
            $I->cli(['eval','"var_dump(\$_SERVER[\'X_FOO\'] ?? \'Not set\');"']);
            $I->seeInShellOutput('X_BAR_putenv');
        }finally {
            unset($_ENV['X_FOO']);
            putenv('X_BAZ');
            putenv('X_FOO');
        }

    }
    
    /**
     * @test
     */
    public function inheriting_env_on_php_less_than_71000(ClimoduleTester $I)
    {
        if(PHP_VERSION_ID > 70100) {
            $I->markTestSkipped('This test should only run on PHP < 7.1');
            return;
        }
        
        // putenv won't work on PHP < 7.1, and it never did either.
        $_ENV['X_FOO'] = 'X_BAR';
    
        try {
            $I->cli(['eval','"var_dump(\$_SERVER[\'X_FOO\'] ?? \'Not set\');"']);
            $I->seeInShellOutput('X_BAR');
        }finally {
            unset($_ENV['X_FOO']);
        }

    }
    
    /**
     * @test
     */
    public function that_wp_cli_config_variables_dont_prevent_inheriting_the_environment(ClimoduleTester $I)
    {
        // This will set a custom env variable in WPCLI::buildProcessEnv()
        // which will be inherited by the child process.
        // This will cause proc_open to not receive to current env by default.
        $I->setCliEnv('disable-auto-check-update','1');
    
        putenv('X_BOO=BAM');
    
        try {
            $I->cli(['eval','"var_dump(\$_SERVER[\'X_BOO\'] ?? \'Not set\');"']);
            $I->seeInShellOutput('BAM');
    
            $I->cli(['eval','"var_dump(\$_SERVER[\'WP_CLI_DISABLE_AUTO_CHECK_UPDATE\'] ?? \'Not set\');"']);
            $I->seeInShellOutput('1');
        }finally {
            putenv('X_BOO');
        }

    }
    
    /**
     * @test
     */
    public function that_per_process_environment_variables_can_be_set(ClimoduleTester $I)
    {
        $I->cli(['eval','"var_dump(\$_SERVER[\'BAZ\'] ?? \'Not set\');"'], ['BAZ' => 'BIZ']);
        $I->seeInShellOutput('BIZ');
        
        putenv('BAZ=global_BIZ');
    
        try {
            // per process has priority.
            $I->cli(['eval','"var_dump(\$_SERVER[\'BAZ\'] ?? \'Not set\');"'], ['BAZ' => 'BIZ']);
            $I->seeInShellOutput('BIZ');
        }finally {
            putenv('BAZ');
        }

    }
    
    /**
     * @test
     */
    public function that_global_env_variables_can_be_set_in_the_module(ClimoduleTester $I)
    {
        $I->haveInShellEnvironment(['FOO' => 'BAR']);
        
        $I->cli(['eval','"var_dump(\$_SERVER[\'FOO\'] ?? \'Not set\');"']);
        $I->seeInShellOutput('BAR');
        
        $I->haveInShellEnvironment(['BAZ' => 'BIZ']);
        // Present for all commands
        $I->cli(['eval','"var_dump(\$_SERVER[\'FOO\'] ?? \'Not set\');"']);
        $I->seeInShellOutput('BAR');
        // Merged between calls
        $I->cli(['eval','"var_dump(\$_SERVER[\'BAZ\'] ?? \'Not set\');"']);
        $I->seeInShellOutput('BIZ');
        
        putenv('FOO=global_BAR');
    
        try {
            $I->cli(['eval','"var_dump(\$_SERVER[\'FOO\'] ?? \'Not set\');"']);
            $I->seeInShellOutput('BAR');
    
            $I->cli(['eval','"var_dump(\$_SERVER[\'FOO\'] ?? \'Not set\');"'], ['FOO' => 'BAR_PROCESS']);
            $I->seeInShellOutput('BAR_PROCESS');
        }finally {
            putenv('FOO');
        }
        
    }
    
    /**
     * @test
     */
    public function that_global_env_variables_can_be_passed_from_the_config_file(ClimoduleTester $I)
    {
        // This emulates having a FOO key under the env config.
        $I->setCliEnv('FOO', 'BAR');
        $I->setCliEnv('disable-auto-check-update','1');
        
        $I->cli(['eval','"var_dump(\$_SERVER[\'FOO\'] ?? \'Not set\');"']);
        $I->seeInShellOutput('BAR');
        
        $I->cli(['eval','"var_dump(\$_SERVER[\'disable-auto-check-update\'] ?? \'Not set\');"']);
        $I->seeInShellOutput('Not set');
    
        $I->cli(['eval','"var_dump(\$_SERVER[\'WP_CLI_DISABLE_AUTO_CHECK_UPDATE\'] ?? \'Not set\');"']);
        $I->seeInShellOutput('1');
        
        putenv('FOO=global_BAR');
        
        try {
            // per process putenv() DOES NOT HAVE priority. Use $I->haveInShellEnvironment() for that.
            $I->cli(['eval','"var_dump(\$_SERVER[\'FOO\'] ?? \'Not set\');"']);
            $I->seeInShellOutput('BAR');
    
            putenv('FOO');
            
            $I->haveInShellEnvironment(['FOO' => 'BAR_SHELL_ENV']);
    
            // per process global env variables still have priority.
            $I->cli(['eval','"var_dump(\$_SERVER[\'FOO\'] ?? \'Not set\');"']);
            $I->seeInShellOutput('BAR_SHELL_ENV');
            
            // per process env variables still have priority.
            $I->cli(['eval','"var_dump(\$_SERVER[\'FOO\'] ?? \'Not set\');"'], ['FOO' => 'BAR_PROCESS']);
            $I->seeInShellOutput('BAR_PROCESS');
        }finally {
            putenv('FOO');
        }
    }
}
