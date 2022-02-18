<?php

namespace Codeception\TestCase;

use Codeception\Module\WPLoader;
use lucatume\WPBrowser\Traits\WithForks;
use lucatume\WPBrowser\Traits\WithMonkeyPatching;

class WPTestCaseTest extends \Codeception\Test\Unit
{
    use WithForks;
    use WithMonkeyPatching;

    /**
     * It should load the includes/bootstrap file when WPLoader did not init
     *
     * @test
     */
    public function should_load_the_includes_bootstrap_file_when_wp_loader_did_not_init()
    {
        $result = $this->inAFork(function () {
            $this->patchFileOnce(
                __DIR__ . '/../../../../src/includes/bootstrap.php',
                '<?php define("BOOTSTRAP_LOADED",true);'
            );

            WPLoader::$didInit = false;
            new WPTestCase();

            return defined('BOOTSTRAP_LOADED') ? 'defined' : 'undefined';
        });

        $this->assertEquals('defined', $result, 'The DIR_TESTDATA const should be defined by the bootstrap file.');
    }

    /**
     * It should not load the includes/bootstrap file when WPLoader did init
     *
     * @test
     */
    public function should_not_load_the_includes_bootstrap_file_when_wp_loader_did_init()
    {
        $result = $this->inAFork(function () {
            $this->patchFileOnce(
                __DIR__ . '/../../../../src/includes/bootstrap.php',
                '<?php define("BOOTSTRAP_LOADED",true);'
            );

            WPLoader::$didInit = true;
            new WPTestCase();

            return defined('BOOTSTRAP_LOADED') ? 'defined' : 'undefined';
        });

        $this->assertEquals('undefined', $result, 'The DIR_TESTDATA const should be defined by the bootstrap file.');
    }

    /**
     * It should not load the includes/bootstrap file when env var is set
     *
     * @test
     */
    public function should_not_load_the_includes_bootstrap_file_when_env_var_is_set()
    {
        $result = $this->inAFork(function () {
            $this->patchFileOnce(
                __DIR__ . '/../../../../src/includes/bootstrap.php',
                '<?php define("BOOTSTRAP_LOADED",true);'
            );

            putenv('WPTESTCASE_NO_INIT=1');
            WPLoader::$didInit = false;
            new WPTestCase();

            return defined('BOOTSTRAP_LOADED') ? 'defined' : 'undefined';
        });

        $this->assertEquals('undefined', $result, 'The DIR_TESTDATA const should be defined by the bootstrap file.');
    }
}
