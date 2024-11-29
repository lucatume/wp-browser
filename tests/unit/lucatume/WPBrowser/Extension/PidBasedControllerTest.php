<?php


namespace Unit\lucatume\WPBrowser\Extension;

use lucatume\WPBrowser\Extension\PidBasedController;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use Symfony\Component\Process\Process;

class PidBasedControllerTest extends \Codeception\Test\Unit
{
    public function test_isProcessRunning_on_posix():void{
        $testClass = new class {
            use PidBasedController;

            public function openIsProcessRunning(string $pidFile):bool{
                return $this->isProcessRunning($pidFile);
            }
        };
        $hash = md5(microtime());
        $pidFile = sys_get_temp_dir()."/test-{$hash}.pid";
        $pid = posix_getpid();
        if(!file_put_contents($pidFile,$pid)){
            $this->fail('Could not write pid to file '.$pidFile);
        }

        $this->assertTrue($testClass->openIsProcessRunning($pidFile));
        $this->assertFileExists($pidFile);
    }

    public function test_isProcessRunning_returns_false_if_pid_file_not_exists():void{
        $testClass = new class {
            use PidBasedController;

            public function openIsProcessRunning(string $pidFile):bool{
                return $this->isProcessRunning($pidFile);
            }
        };
        $pid = posix_getpid();

        $this->assertFalse($testClass->openIsProcessRunning(__DIR__ .'/test.pid'));
    }

    public function test_isProcessRunning_throws_if_pid_file_cannot_be_read():void{
        $testClass = new class {
            use PidBasedController;

            public function openIsProcessRunning(string $pidFile):bool{
                return $this->isProcessRunning($pidFile);
            }
        };
        $pid = posix_getpid();
        $hash = md5(microtime());
        $pidFile = sys_get_temp_dir()."/test-{$hash}.pid";
        $pid = posix_getpid();
        if(!file_put_contents($pidFile,$pid)){
            $this->fail('Could not write pid to file '.$pidFile);
        }
        // Change the file mode to not be readable by the current user.
        chmod($pidFile,0000);

        $this->assertFalse($testClass->openIsProcessRunning($pidFile));
    }

    public function test_isProcessRunning_returns_false_if_process_is_not_running():void{
        $testClass = new class {
            use PidBasedController;

            public function openIsProcessRunning(string $pidFile):bool{
                return $this->isProcessRunning($pidFile);
            }
        };
        $hash = md5(microtime());
        $pidFile = sys_get_temp_dir()."/test-{$hash}.pid";
        $process = new Process(['echo', '23']);
        $process->start();
        $pid = $process->getPid();
        $process->wait();
        if(!file_put_contents($pidFile,$pid)){
            $this->fail('Could not write pid to file '.$pidFile);
        }

        $this->assertFalse($testClass->openIsProcessRunning($pidFile));
        $this->assertFileNotExists($pidFile);
    }
}
