<?php


namespace Unit\lucatume\WPBrowser\Process;

use Codeception\Exception\ModuleException;
use Codeception\Test\Unit;
use CompileError;
use DateTimeImmutable;
use DateTimeZone;
use Error;
use ErrorException;
use Generator;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Process\StderrStream;
use lucatume\WPBrowser\WordPress\InstallationException;
use ParseError;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;

class StderrStreamTest extends Unit
{
    use SnapshotAssertions;

    public function fromStreamDataProvider(): Generator
    {
        yield 'empty stream' => [
            'stream' => ''
        ];

        $warning = <<<EOT
[17-Mar-2023 10:21:16 Europe/Paris] PHP Warning:  Undefined array key "__composer_autoload_file" in /src/Process/Protocol/Control.php on line 16
[17-Mar-2023 10:21:16 Europe/Paris] PHP Stack trace:
[17-Mar-2023 10:21:16 Europe/Paris] PHP   1. {main}() /src/Process/Worker/worker-script.php:0
[17-Mar-2023 10:21:16 Europe/Paris] PHP   2. lucatume\WPBrowser\Process\Protocol\Request::fromPayload(\$encodedPayload = 'JDIzNzcNCmE6NDp7czoxMjoiYXV0b2xvYWRGaWxlIjtzOjUwOiIvVXNlcnMvbHVjYXR1bWUvb3NzL3dwLWJyb3dzZXIvdmVuZG9yL2F1dG9sb2FkLnBocCI7czoxMjoicmVxdWlyZUZpbGVzIjthOjE6e2k6MDtzOjg0OiIvVXNlcnMvbHVjYXR1bWUvb3NzL3dwLWJyb3dzZXIvdGVzdHMvdW5pdC9sdWNhdHVtZS9XUEJyb3dzZXIvTW9kdWxlL1dQTG9hZGVyVGVzdC5waHAiO31zOjM6ImN3ZCI7czo4MDoiL1VzZXJzL2x1Y2F0dW1lL29zcy93cC1icm93c2VyL3Zhci90bXAvd3Bsb2FkZXJfMDU1ZDhmMDYxZWM5NDhkY2VlM2Y4YWM5OTIzYzIxMzIiO3M6MTc6ImNvZGVjZXB0aW9uQ29uZmlnIjthOjE3OntzOjU6ImFjdG9yIjtzOjY6IlRlc3RlciI7czo1OiJwYXRocyI7YTo1Ontz'...) /src/Process/Worker/worker-script.php:13
[17-Mar-2023 10:21:16 Europe/Paris] PHP   3. lucatume\WPBrowser\Process\Protocol\Control->__construct(\$controlArray = ['autoloadFile' => '/vendor/autoload.php', 'requireFiles' => [0 => '/tests/unit/lucatume/WPBrowser/Module/WPLoaderTest.php'], 'cwd' => '/var/tmp/wploader_055d8f061ec948dcee3f8ac9923c2132', 'codeceptionConfig' => ['actor' => 'Tester', 'paths' => [...], 'settings' => [...], 'params' => [...], 'bootstrap' => '_bootstrap.php', 'coverage' => [...], 'wpFolder' => 'var/wordpress', 'extensions' => [...], 'actor_suffix' => 'Tester', 'support_namespace' => NULL, 'namespace' => '', 'include' => [...], 'extends' => NULL, 'suites' => [...], 'modules' => [...], 'groups' => [...], 'gherkin' => [...]]]) /src/Process/Protocol/Request.php:54
[1
EOT;

        yield 'Warning in stream' => [$warning, ErrorException::class, E_WARNING];

        $fatalError = <<<EOT
[17-Mar-2023 10:21:16 Europe/Paris] PHP Fatal error:  Uncaught Error: Class "lucatume\WPBrowser\Utils\ErrorHandling" not found in /src/Process/SerializableThrowable.php:24
Stack trace:
#0 /src/Process/Worker/worker-script.php(18): lucatume\WPBrowser\Process\SerializableThrowable->__construct(Object(Error))
#1 {main}
  thrown in /src/Process/SerializableThrowable.php on line 24
EOT;

        yield 'Fatal error in stream' => [$fatalError, Error::class];

        $exception = <<<EOT
[17-Mar-2023 16:23:28 Europe/Paris] PHP Fatal error:  Uncaught RuntimeException: This file is not meant to be executed directly. in /src/Process/Worker/worker-script.php:12
Stack trace:
#0 {main}
  thrown in /src/Process/Worker/worker-script.php on line 12
EOT;

        yield 'Exception in stream' => [$exception, \RuntimeException::class];

        $namespacedException = <<<EOT
[17-Mar-2023 16:32:35 Europe/Paris] PHP Fatal error:  Uncaught PHPUnit\Framework\MockObject\RuntimeException: Mocking did not work. in /src/Process/Worker/worker-script.php:15
Stack trace:
#0 {main}
  thrown in /src/Process/Worker/worker-script.php on line 15
EOT;

        yield 'Namespaced exception in stream' => [
            $namespacedException,
            \PHPUnit\Framework\MockObject\RuntimeException::class
        ];

        $randomNoise = <<<EOT
Lorem ipsum dolor sit amet, consectetur adipiscing elit.
Donec auctor, nisl eget aliquam tincidunt, nunc nisl aliquet nunc, eget aliquam nis
EOT;

        yield 'random noise in stream' => [$randomNoise, null];

        yield 'random noise and warning in stream' => [
            $randomNoise . PHP_EOL . $warning,
            ErrorException::class,
            E_WARNING
        ];

        yield 'random noise and fatal error in stream' => [
            $randomNoise . PHP_EOL . $fatalError,
            Error::class
        ];

        yield 'random noise and exception in stream' => [$randomNoise . PHP_EOL . $exception, \RuntimeException::class];

        yield 'random noise and namespaced exception in stream' => [
            $randomNoise . PHP_EOL . $namespacedException,
            \PHPUnit\Framework\MockObject\RuntimeException::class
        ];

        $nestedException = <<<EOT
[17-Mar-2023 16:54:06 Europe/Paris] PHP Fatal error:  Uncaught lucatume\WPBrowser\Exceptions\RuntimeException: For Reasons in /includes/core-phpunit/includes/bootstrap.php:261
Stack trace:
#0 /src/Module/WPLoader.php(652): require()
#1 /src/Module/WPLoader.php(426): lucatume\WPBrowser\Module\WPLoader->installAndBootstrapInstallation()
#2 /src/Module/WPLoader.php(368): lucatume\WPBrowser\Module\WPLoader->_loadWordpress()
#3 closure://static function () use (\$rootDir, \$wpLoader1) {
            \$wpLoader1->_initialize();
            \PHPUnit\Framework\Assert::assertEquals(\$rootDir . '/test/wordpress/', \$wpLoader1->_getConfig('wpRootFolder'));
            \PHPUnit\Framework\Assert::assertEquals(\$rootDir . '/test/wordpress/', \$wpLoader1->getWpRootFolder());
        }(3): lucatume\WPBrowser\Module\WPLoader->_initialize()
#4 [internal function]: lucatume\WPBrowser\Module\WPLoaderTest::{closure}()
#5 /vendor/opis/closure/src/SerializableClosure.php(109): call_user_func_array(Object(Closure), Array)
#6 /src/Process/Worker/worker-script.php(15): lucatume\WPBrowser\Opis\Closure\SerializableClosure->__invoke()
#7 {main}
  thrown in /includes/core-phpunit/includes/bootstrap.php on line 261
EOT;

        yield 'nested exception in stream' => [$nestedException, RuntimeException::class];

        yield 'nested exception and random noise in stream' => [
            $nestedException . PHP_EOL . $randomNoise,
            RuntimeException::class
        ];

        yield 'all the things in stream' => [
            $randomNoise . PHP_EOL . $fatalError . PHP_EOL . $warning . PHP_EOL . $exception . PHP_EOL . $namespacedException . PHP_EOL . $nestedException,
            Error::class
        ];

        yield 'Runtime warning in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Runtime warning:  count(): Parameter must be an array or an object that implements Countable in /src/Process/Worker/worker-script.php on line 15',
            ErrorException::class,
            E_WARNING
        ];

        yield 'Parse error in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Parse error:  syntax error, unexpected \'$\' in /src/Process/Worker/worker-script.php on line 15',
            ParseError::class
        ];

        yield 'Runtime notice in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Runtime notice:  Undefined variable: foo in /src/Process/Worker/worker-script.php on line 15',
            ErrorException::class,
            E_NOTICE
        ];

        yield 'Notice in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Notice:  Undefined variable: foo in /src/Process/Worker/worker-script.php on line 15',
            ErrorException::class,
            E_NOTICE
        ];

        yield 'Strict standards in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Strict Standards:  Declaration of Foo::bar() should be compatible with Bar::bar() in /src/Process/Worker/worker-script.php on line 15',
            ErrorException::class,
            E_STRICT
        ];

        yield 'Recoverable error in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Recoverable error:  Object of class stdClass could not be converted to string in /src/Process/Worker/worker-script.php on line 15',
            ErrorException::class,
            E_RECOVERABLE_ERROR
        ];

        yield 'Deprecated in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Deprecated:  Methods with the same name as their class will not be constructors in a future version of PHP; Foo has a deprecated constructor in /src/Process/Worker/worker-script.php on line 15',
            ErrorException::class,
            E_DEPRECATED
        ];

        yield 'Core error in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Core error:  Unknown: Cannot use output buffering in output buffering display handlers in /src/Process/Worker/worker-script.php on line 15',
            ErrorException::class,
            E_CORE_ERROR
        ];

        yield 'Core warning in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Core warning:  Unknown: Cannot use output buffering in output buffering display handlers in /src/Process/Worker/worker-script.php on line 15',
            ErrorException::class,
            E_CORE_WARNING
        ];

        yield 'Compile error in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Compile error:  Cannot use output buffering in output buffering display handlers in /src/Process/Worker/worker-script.php on line 15',
            CompileError::class
        ];

        yield 'Compile warning in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Compile warning:  Cannot use output buffering in output buffering display handlers in /src/Process/Worker/worker-script.php on line 15',
            ErrorException::class,
            E_COMPILE_WARNING
        ];

        yield 'User error in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP User error:  Cannot use output buffering in output buffering display handlers in /src/Process/Worker/worker-script.php on line 15',
            ErrorException::class,
            E_USER_ERROR
        ];

        yield 'User warning in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP User warning:  Cannot use output buffering in output buffering display handlers in /src/Process/Worker/worker-script.php on line 15',
            ErrorException::class,
            E_USER_WARNING
        ];

        yield 'User notice in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP User notice:  Cannot use output buffering in output buffering display handlers in /src/Process/Worker/worker-script.php on line 15',
            ErrorException::class,
            E_USER_NOTICE
        ];

        yield 'User deprecated in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP User deprecated:  Cannot use output buffering in output buffering display handlers in /src/Process/Worker/worker-script.php on line 15',
            ErrorException::class,
            E_USER_DEPRECATED
        ];

        yield 'Weird error in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Weird error:  Cannot use output buffering in output buffering display handlers in /src/Process/Worker/worker-script.php on line 15',
            ErrorException::class,
            E_ERROR
        ];

        $exceptionWithLineBreaks = <<<EXCEPTION
[01-Apr-2023 19:14:58 Europe/Paris] PHP Fatal error:  Uncaught Codeception\Exception\ModuleException: lucatume\WPBrowser\Module\WPLoader: WordPress bootstrap failed.
Error: Looks like you're using PHPUnit 5.0.0. WordPress requires at least PHPUnit 5.7.21.
Please use the latest PHPUnit version supported for the PHP version you are running the tests on.
 in /src/Module/WPLoader.php:475
Stack trace:
#0 [internal function]: lucatume\WPBrowser\Module\WPLoader::lucatume\WPBrowser\Module\{closure}('Error: Looks li...', 9)
#1 {main}
  thrown in /src/Module/WPLoader.php on line 475
EXCEPTION;

        yield 'Exception with line breaks in message' => [
            $exceptionWithLineBreaks,
            ModuleException::class
        ];

        $fatalErrorFromIsolatedAssertion = <<< ERROR
PHP Fatal error:  Uncaught lucatume\WPBrowser\WordPress\InstallationException: WordPress is not installed. in /src/WordPress/InstallationException.php:54
Stack trace:
#0 /src/WordPress/LoadSandbox.php(78): lucatume\WPBrowser\WordPress\InstallationException::becauseWordPressIsNotInstalled()
#1 [internal function]: lucatume\WPBrowser\WordPress\LoadSandbox->obCallback('', 9)
#2 /var/tmp/wploader_4affd42539aa6da4efba7a0de0de5319/wp-includes/functions.php(5279): ob_end_flush()
#3 [internal function]: wp_ob_end_flush_all('')
#4 /var/tmp/wploader_4affd42539aa6da4efba7a0de0de5319/wp-includes/class-wp-hook.php(308): call_user_func_array('wp_ob_end_flush...', Array)
#5 /var/tmp/wploader_4affd42539aa6da4efba7a0de0de5319/wp-includes/class-wp-hook.php(332): WP_Hook->apply_filters('', Array)
#6 /var/tmp/wploader_4affd42539aa6da4efba7a0de0de5319/wp-includes/plugin.php(517): WP_Hook->do_action(Array)
#7 /var/tmp/wploader_4affd42539aa6da4efba7a0de0de5319/wp-includes/load.php(1124): do_action('shutdown')
#8 [internal function]: shutdown_action_hook()
#9 {main}
  thrown in /src/WordPress/InstallationException.php on line 54
ERROR;

        yield 'Fatal error from isolatedAssertion' => [
            $fatalErrorFromIsolatedAssertion,
            InstallationException::class
        ];
    }

    /**
     * @dataProvider fromStreamDataProvider
     */
    public function testParsesStreamCorrectly(
        string $stream,
        ?string $expectedThrowableClass = null,
        ?int $expectedSeverity = null
    ): void {
        $currentDateTime = new DateTimeImmutable('2023-03-17 16:54:06', new DateTimeZone('Europe/Paris'));
        $stderrStream = new StderrStream($stream, StderrStream::RELATIVE_PATHNAMES, $currentDateTime);
        $errors = $stderrStream->getParsed();

        $encoded = json_encode($errors, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        codecept_debug($encoded);
        $this->assertMatchesJsonSnapshot($encoded);
        if ($expectedThrowableClass) {
            $throwable = $stderrStream->getThrowable();
            $this->assertInstanceOf($expectedThrowableClass, $throwable);
            if ($expectedSeverity) {
                $this->assertSame($expectedSeverity, $throwable->getSeverity());
            }
        }
    }
}
