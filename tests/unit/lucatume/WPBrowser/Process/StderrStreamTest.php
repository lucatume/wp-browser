<?php


namespace Unit\lucatume\WPBrowser\Process;

use Codeception\Test\Unit;
use Generator;
use lucatume\WPBrowser\Process\StderrStream;
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
[17-Mar-2023 10:21:16 Europe/Paris] PHP Warning:  Undefined array key "__composer_autoload_file" in /Users/lucatume/oss/wp-browser/src/Process/Protocol/Control.php on line 16
[17-Mar-2023 10:21:16 Europe/Paris] PHP Stack trace:
[17-Mar-2023 10:21:16 Europe/Paris] PHP   1. {main}() /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php:0
[17-Mar-2023 10:21:16 Europe/Paris] PHP   2. lucatume\WPBrowser\Process\Protocol\Request::fromPayload(\$encodedPayload = 'JDIzNzcNCmE6NDp7czoxMjoiYXV0b2xvYWRGaWxlIjtzOjUwOiIvVXNlcnMvbHVjYXR1bWUvb3NzL3dwLWJyb3dzZXIvdmVuZG9yL2F1dG9sb2FkLnBocCI7czoxMjoicmVxdWlyZUZpbGVzIjthOjE6e2k6MDtzOjg0OiIvVXNlcnMvbHVjYXR1bWUvb3NzL3dwLWJyb3dzZXIvdGVzdHMvdW5pdC9sdWNhdHVtZS9XUEJyb3dzZXIvTW9kdWxlL1dQTG9hZGVyVGVzdC5waHAiO31zOjM6ImN3ZCI7czo4MDoiL1VzZXJzL2x1Y2F0dW1lL29zcy93cC1icm93c2VyL3Zhci90bXAvd3Bsb2FkZXJfMDU1ZDhmMDYxZWM5NDhkY2VlM2Y4YWM5OTIzYzIxMzIiO3M6MTc6ImNvZGVjZXB0aW9uQ29uZmlnIjthOjE3OntzOjU6ImFjdG9yIjtzOjY6IlRlc3RlciI7czo1OiJwYXRocyI7YTo1Ontz'...) /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php:13
[17-Mar-2023 10:21:16 Europe/Paris] PHP   3. lucatume\WPBrowser\Process\Protocol\Control->__construct(\$controlArray = ['autoloadFile' => '/Users/lucatume/oss/wp-browser/vendor/autoload.php', 'requireFiles' => [0 => '/Users/lucatume/oss/wp-browser/tests/unit/lucatume/WPBrowser/Module/WPLoaderTest.php'], 'cwd' => '/Users/lucatume/oss/wp-browser/var/tmp/wploader_055d8f061ec948dcee3f8ac9923c2132', 'codeceptionConfig' => ['actor' => 'Tester', 'paths' => [...], 'settings' => [...], 'params' => [...], 'bootstrap' => '_bootstrap.php', 'coverage' => [...], 'wpFolder' => 'var/wordpress', 'extensions' => [...], 'actor_suffix' => 'Tester', 'support_namespace' => NULL, 'namespace' => '', 'include' => [...], 'extends' => NULL, 'suites' => [...], 'modules' => [...], 'groups' => [...], 'gherkin' => [...]]]) /Users/lucatume/oss/wp-browser/src/Process/Protocol/Request.php:54
[1
EOT;

        yield 'Warning in stream' => [$warning, \ErrorException::class, E_WARNING];

        $fatalError = <<<EOT
[17-Mar-2023 10:21:16 Europe/Paris] PHP Fatal error:  Uncaught Error: Class "lucatume\WPBrowser\Utils\ErrorHandling" not found in /Users/lucatume/oss/wp-browser/src/Process/SerializableThrowable.php:24
Stack trace:
#0 /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php(18): lucatume\WPBrowser\Process\SerializableThrowable->__construct(Object(Error))
#1 {main}
  thrown in /Users/lucatume/oss/wp-browser/src/Process/SerializableThrowable.php on line 24
EOT;

        yield 'Fatal error in stream' => [$fatalError, \Error::class];

        $exception = <<<EOT
[17-Mar-2023 16:23:28 Europe/Paris] PHP Fatal error:  Uncaught RuntimeException: This file is not meant to be executed directly. in /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php:12
Stack trace:
#0 {main}
  thrown in /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php on line 12
EOT;

        yield 'Exception in stream' => [$exception, \RuntimeException::class];

        $namespacedException = <<<EOT
[17-Mar-2023 16:32:35 Europe/Paris] PHP Fatal error:  Uncaught PHPUnit\Framework\MockObject\RuntimeException: Mocking did not work. in /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php:15
Stack trace:
#0 {main}
  thrown in /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php on line 15
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
            \ErrorException::class,
            E_WARNING
        ];

        yield 'random noise and fatal error in stream' => [
            $randomNoise . PHP_EOL . $fatalError,
            \Error::class
        ];

        yield 'random noise and exception in stream' => [$randomNoise . PHP_EOL . $exception, \RuntimeException::class];

        yield 'random noise and namespaced exception in stream' => [
            $randomNoise . PHP_EOL . $namespacedException,
            \PHPUnit\Framework\MockObject\RuntimeException::class
        ];

        $nestedException = <<<EOT
[17-Mar-2023 16:54:06 Europe/Paris] PHP Fatal error:  Uncaught PHPUnit\TextUI\RuntimeException: For Reasons in /Users/lucatume/oss/wp-browser/includes/core-phpunit/includes/bootstrap.php:261
Stack trace:
#0 /Users/lucatume/oss/wp-browser/src/Module/WPLoader.php(652): require()
#1 /Users/lucatume/oss/wp-browser/src/Module/WPLoader.php(426): lucatume\WPBrowser\Module\WPLoader->installAndBootstrapInstallation()
#2 /Users/lucatume/oss/wp-browser/src/Module/WPLoader.php(368): lucatume\WPBrowser\Module\WPLoader->_loadWordpress()
#3 closure://static function () use (\$rootDir, \$wpLoader1) {
            \$wpLoader1->_initialize();
            \PHPUnit\Framework\Assert::assertEquals(\$rootDir . '/test/wordpress/', \$wpLoader1->_getConfig('wpRootFolder'));
            \PHPUnit\Framework\Assert::assertEquals(\$rootDir . '/test/wordpress/', \$wpLoader1->getWpRootFolder());
        }(3): lucatume\WPBrowser\Module\WPLoader->_initialize()
#4 [internal function]: lucatume\WPBrowser\Module\WPLoaderTest::{closure}()
#5 /Users/lucatume/oss/wp-browser/vendor/opis/closure/src/SerializableClosure.php(109): call_user_func_array(Object(Closure), Array)
#6 /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php(15): Opis\Closure\SerializableClosure->__invoke()
#7 {main}
  thrown in /Users/lucatume/oss/wp-browser/includes/core-phpunit/includes/bootstrap.php on line 261
EOT;

        yield 'nested exception in stream' => [$nestedException, \PHPUnit\TextUI\RuntimeException::class];

        yield 'nested exception and random noise in stream' => [
            $nestedException . PHP_EOL . $randomNoise,
            \PHPUnit\TextUI\RuntimeException::class
        ];

        yield 'all the things in stream' => [
            $randomNoise . PHP_EOL . $fatalError . PHP_EOL . $warning . PHP_EOL . $exception . PHP_EOL . $namespacedException . PHP_EOL . $nestedException,
            \Error::class
        ];

        yield 'Runtime warning in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Runtime warning:  count(): Parameter must be an array or an object that implements Countable in /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php on line 15',
            \ErrorException::class,
            E_WARNING
        ];

        yield 'Parse error in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Parse error:  syntax error, unexpected \'$\' in /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php on line 15',
            \ParseError::class
        ];

        yield 'Runtime notice in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Runtime notice:  Undefined variable: foo in /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php on line 15',
            \ErrorException::class,
            E_NOTICE
        ];

        yield 'Notice in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Notice:  Undefined variable: foo in /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php on line 15',
            \ErrorException::class,
            E_NOTICE
        ];

        yield 'Strict standards in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Strict Standards:  Declaration of Foo::bar() should be compatible with Bar::bar() in /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php on line 15',
            \ErrorException::class,
            E_STRICT
        ];

        yield 'Recoverable error in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Recoverable error:  Object of class stdClass could not be converted to string in /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php on line 15',
            \ErrorException::class,
            E_RECOVERABLE_ERROR
        ];

        yield 'Deprecated in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Deprecated:  Methods with the same name as their class will not be constructors in a future version of PHP; Foo has a deprecated constructor in /Users/lucatume/oss/wp-browser/src/Process/Worker/worker-script.php on line 15',
            \ErrorException::class,
            E_DEPRECATED
        ];

        yield 'Core error in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Core error:  Unknown: Cannot use output buffering in output buffering display handlers in Unknown on line 0',
            \ErrorException::class,
            E_CORE_ERROR
        ];

        yield 'Core warning in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Core warning:  Unknown: Cannot use output buffering in output buffering display handlers in Unknown on line 0',
            \ErrorException::class,
            E_CORE_WARNING
        ];

        yield 'Compile error in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Compile error:  Cannot use output buffering in output buffering display handlers in Unknown on line 0',
            \CompileError::class
        ];

        yield 'Compile warning in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Compile warning:  Cannot use output buffering in output buffering display handlers in Unknown on line 0',
            \ErrorException::class,
            E_COMPILE_WARNING
        ];

        yield 'User error in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP User error:  Cannot use output buffering in output buffering display handlers in Unknown on line 0',
            \ErrorException::class,
            E_USER_ERROR
        ];

        yield 'User warning in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP User warning:  Cannot use output buffering in output buffering display handlers in Unknown on line 0',
            \ErrorException::class,
            E_USER_WARNING
        ];

        yield 'User notice in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP User notice:  Cannot use output buffering in output buffering display handlers in Unknown on line 0',
            \ErrorException::class,
            E_USER_NOTICE
        ];

        yield 'User deprecated in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP User deprecated:  Cannot use output buffering in output buffering display handlers in Unknown on line 0',
            \ErrorException::class,
            E_USER_DEPRECATED
        ];

        yield 'Weird erorr in stream' => [
            '[17-Mar-2023 16:54:06 Europe/Paris] PHP Weird error:  Cannot use output buffering in output buffering display handlers in Unknown on line 0',
            \ErrorException::class,
            E_ERROR
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
        $stderrStream = new StderrStream($stream, StderrStream::RELATIVE_PATHNAMES);
        $errors = $stderrStream->getParsed();

        $this->assertMatchesJsonSnapshot(json_encode($errors, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        if ($expectedThrowableClass) {
            $throwable = $stderrStream->getThrowable();
            $this->assertInstanceOf($expectedThrowableClass, $throwable);
            if ($expectedSeverity) {
                $this->assertSame($expectedSeverity, $throwable->getSeverity());
            }
        }
    }
}
