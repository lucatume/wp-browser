<?php

namespace lucatume\WPBrowser\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use lucatume\WPBrowser\TestCase\WPTestCase;
use lucatume\WPBrowser\Tests\Traits\LoopIsolation;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Env;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\Utils\Random;
use lucatume\WPBrowser\WordPress\Database\MysqlDatabase;
use lucatume\WPBrowser\WordPress\Installation;
use PHPUnit\Framework\Assert;

class WPTestCaseStrictTest extends Unit
{
    use LoopIsolation;
    use TmpFilesCleanup;

    private function module(array $moduleContainerConfig = [], ?array $moduleConfig = null): WPLoader
    {
        $this->mockModuleContainer = new ModuleContainer(new Di(), $moduleContainerConfig);
        return new WPLoader($this->mockModuleContainer, ($moduleConfig ?? $this->config));
    }

    public function nonBooleanVAluesProvider(): array
    {
        return [
            'int' => [1],
            'float' => [1.1],
            'array' => [[]],
            'object' => [new \stdClass()],
            'true string' => ['true'],
            'false string' => ['false'],
        ];
    }

    /**
     * @dataProvider nonBooleanVAluesProvider
     */
    public function test_will_throw_if_beStrictAboutWpdbConnectionId_is_not_boolean($value): void
    {
        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessage('The `beStrictAboutWpdbConnectionId` configuration parameter must be a boolean.');

        $this->config = [
            'wpRootFolder' => __DIR__,
            'dbUrl' => 'mysql://root:root@mysql:3306/wordpress',
            'beStrictAboutWpdbConnectionId' => $value
        ];
        $this->module();
    }

    public function test_will_fail_if_db_connection_closed_during_setup_before_class(): void
    {
        $wpRootDir = FS::tmpDir('wploader_');
        $dbName = Random::dbName();
        $dbHost = Env::get('WORDPRESS_DB_HOST');
        $dbUser = Env::get('WORDPRESS_DB_USER');
        $dbPassword = Env::get('WORDPRESS_DB_PASSWORD');
        $db = new MysqlDatabase($dbName, $dbUser, $dbPassword, $dbHost);
        Installation::scaffold($wpRootDir);
        $db->create();
        $testcaseFile = $wpRootDir . '/BreakingTest.php';
        $testCaseFileContents = <<< PHP
            <?php
            
            use lucatume\WPBrowser\TestCase\WPTestCase;

            class BreakingTest extends WPTestCase
            {
                public static function setUpBeforeClass():void
                {
                    global \$wpdb;
                    \$wpdb->close();

                    parent::set_up_before_class();
                }

                public function test_something():void{
                    \$this->assertTrue(true);
                }
            }
        PHP;
        if(!file_put_contents($testcaseFile, $testCaseFileContents, LOCK_EX)) {
            throw new \RuntimeException('Could not write BreakingTest.php.');
        }

        // Run a test using the default value, strict.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl()
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile) {
            $wpLoader->_initialize();
            $connectionId = WPTestCase::getWpdbConnectionId();
            Assert::assertnotEmpty($connectionId);
            Assert::assertTrue(WPTestCase::isStrictAboutWpdbConnectionId());

            require_once $testcaseFile;

            try {
                \BreakingTest::setUpBeforeClass();
            } catch (\Throwable $e) {
                Assert::assertNotSame($connectionId, $GLOBALS['wpdb']->get_var('SELECT CONNECTION_ID()'));
                Assert::assertStringContainsString(
                    'The database connection went away. A `setUpBeforeClassMethod` likely closed the connection',
                    $e->getMessage()
                );
                return;
            }

            Assert::fail('The test should have failed.');
        });

        // Run a test in strict mode.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'beStrictAboutWpdbConnectionId' => true
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile) {
            $wpLoader->_initialize();
            $connectionId = WPTestCase::getWpdbConnectionId();
            Assert::assertnotEmpty($connectionId);
            Assert::assertTrue(WPTestCase::isStrictAboutWpdbConnectionId());

            require_once $testcaseFile;

            try {
                \BreakingTest::setUpBeforeClass();
            } catch (\Throwable $e) {
                Assert::assertNotSame($connectionId, $GLOBALS['wpdb']->get_var('SELECT CONNECTION_ID()'));
                Assert::assertStringContainsString(
                    'The database connection went away. A `setUpBeforeClassMethod` likely closed the connection',
                    $e->getMessage()
                );
                return;
            }

            Assert::fail('The test should have failed.');
        });

        // Run a test in non-strict mode.
        $this->config = [
            'wpRootFolder' => $wpRootDir,
            'dbUrl' => $db->getDbUrl(),
            'beStrictAboutWpdbConnectionId' => false
        ];
        $wpLoader = $this->module();

        $this->assertInIsolation(static function () use ($wpLoader, $testcaseFile) {
            $wpLoader->_initialize();
            $connectionId = WPTestCase::getWpdbConnectionId();
            Assert::assertFalse(WPTestCase::isStrictAboutWpdbConnectionId());

            require_once $testcaseFile;

            \BreakingTest::setUpBeforeClass();
            Assert::assertNotSame($connectionId, $GLOBALS['wpdb']->get_var('SELECT CONNECTION_ID()'));
        });
    }
}
