<?php

namespace lucatume\WPBrowser\Traits;

use Codeception\Test\Unit;
use lucautme\WPBrowser\Acme\Project\NamespacedClassWithStaticVariables;
use lucautme\WPBrowser\Acme\Project\SomeNamespacedClassOne;
use lucautme\WPBrowser\Acme\Project\SomeNamespacedClassTwo;
use lucautme\WPBrowser\Acme\Project\SomeNamespacedClassWithFinalMethods;
use lucautme\WPBrowser\Acme\Project\SomeNamespacedClassWithoutMethods;
use lucautme\WPBrowser\Acme\Project\SomeNamespacedFinalClass;
use ReflectionClass;
use ReflectionMethod;
use SomeGlobalClassOne;
use SomeGlobalClassTwo;
use SomeGlobalClassWithFinalMethods;
use SomeGlobalClassWithoutMethods;
use SomeGlobalClassWithStaticVariables;
use SomeGlobalFinalClass;

use const EXISTING_CONSTANT;
use const lucatume\WPBrowser\Acme\Project\NOT_EXISTING_CONSTANT;

require_once codecept_data_dir('uopz-test/functions.php');
require_once codecept_data_dir('uopz-test/global-classes.php');
require_once codecept_data_dir('uopz-test/namespaced-classes.php');

class UopzFunctionsTest extends Unit
{
    use UopzFunctions;

    /**
     * It should allow setting a function return value
     *
     * @test
     */
    public function should_allow_setting_a_function_return_value(): void
    {
        $this->setFunctionReturn('someTestFunction', 23);

        $this->assertEquals(23, someTestFunction());
    }

    /**
     * It should allow setting the return value of a namespaced function
     *
     * @test
     */
    public function should_allow_setting_the_return_value_of_a_namespaced_function(): void
    {
        $this->setFunctionReturn('lucatume\WPBrowser\Acme\Project\testFunction', 23);

        $this->assertEquals(23, \lucatume\WPBrowser\Acme\Project\testFunction());
    }

    /**
     * It should allow setting the return value of a function with a closure
     *
     * @test
     */
    public function should_allow_setting_the_return_value_of_a_function_with_a_closure(): void
    {
        $this->setFunctionReturn('someTestFunction', static function () {
            return 23;
        }, true);
        $this->setFunctionReturn('lucatume\WPBrowser\Acme\Project\testFunction', static function () {
            return 89;
        }, true);

        $this->assertEquals(23, someTestFunction());
        $this->assertEquals(89, \lucatume\WPBrowser\Acme\Project\testFunction());
    }

    /**
     * It should allow setting the return value of a function to a closure value
     *
     * @test
     */
    public function should_allow_setting_the_return_value_of_a_function_to_a_closure_value(): void
    {
        $closure1 = static function () {
            return 23;
        };
        $this->setFunctionReturn('someTestFunction', $closure1);
        $closure2 = static function () {
            return 89;
        };
        $this->setFunctionReturn('lucatume\WPBrowser\Acme\Project\testFunction', $closure2);

        $this->assertEquals($closure1, someTestFunction());
        $this->assertEquals($closure2, \lucatume\WPBrowser\Acme\Project\testFunction());
    }

    /**
     * It should handle functions with reference arguments
     *
     * @test
     */
    public function should_handle_functions_with_reference_arguments(): void
    {
        $this->setFunctionReturn('someReferenceFunction', null);

        $input = [23, 89];
        someReferenceFunction($input);
        $this->assertEquals([23, 89], $input);

        $this->setFunctionReturn('someReferenceFunction', function (array &$input) {
            $input[] = 'hello';
        }, true);

        someReferenceFunction($input);

        $this->assertEquals([23, 89, 'hello'], $input);

        $this->setFunctionReturn('lucatume\WPBrowser\Acme\Project\someReferenceFunction', null);

        $input = [23, 89];
        \lucatume\WPBrowser\Acme\Project\someReferenceFunction($input);
        $this->assertEquals([23, 89], $input);

        $this->setFunctionReturn('lucatume\WPBrowser\Acme\Project\someReferenceFunction', function (array &$input) {
            $input[] = 'hello';
        }, true);

        \lucatume\WPBrowser\Acme\Project\someReferenceFunction($input);

        $this->assertEquals([23, 89, 'hello'], $input);
    }

    /**
     * It should unset function return value between tests
     *
     * @test
     */
    public function should_unset_function_return_value_between_tests(): void
    {
        $this->assertEquals('test-test-test', someTestFunction());
        $this->assertEquals('test-test-test', \lucatume\WPBrowser\Acme\Project\testFunction());
    }

    /**
     * It should allow unsetting a function return value
     *
     * @test
     */
    public function should_allow_unsetting_a_function_return_value(): void
    {
        $this->setFunctionReturn('someTestFunction', 23);
        $this->setFunctionReturn('lucatume\WPBrowser\Acme\Project\testFunction', 89);

        $this->assertEquals(23, someTestFunction());
        $this->assertEquals(89, \lucatume\WPBrowser\Acme\Project\testFunction());

        $this->unsetFunctionReturn('someTestFunction');
        $this->unsetFunctionReturn('lucatume\WPBrowser\Acme\Project\testFunction');

        $this->assertEquals('test-test-test', someTestFunction());
        $this->assertEquals('test-test-test', \lucatume\WPBrowser\Acme\Project\testFunction());
    }

    /**
     * It should not throw when unsetting a non-set function return value
     *
     * @test
     */
    public function should_not_throw_when_unsetting_a_non_set_function_return_value(): void
    {
        $this->unsetFunctionReturn('someTestFunction');
        $this->unsetFunctionReturn('Acme\Project\testFunction');
    }

    /**
     * It should allow setting an instance method return value
     *
     * @test
     */
    public function should_allow_setting_an_instance_method_return_value(): void
    {
        $this->setMethodReturn(SomeGlobalClassOne::class, 'getValueOne', 23);
        $return23 = function () {
            return 23;
        };
        $this->setMethodReturn(SomeGlobalClassOne::class, 'getValueTwo', $return23);
        $this->setMethodReturn(SomeGlobalClassOne::class, 'getValueThree', function () {
            return 89;
        }, true);
        $this->setMethodReturn(SomeNamespacedClassOne::class, 'getValueOne', 23);
        $return23 = function () {
            return 23;
        };
        $this->setMethodReturn(SomeNamespacedClassOne::class, 'getValueTwo', $return23);
        $this->setMethodReturn(SomeNamespacedClassOne::class, 'getValueThree', function () {
            return 89;
        }, true);

        $globalClass = new SomeGlobalClassOne();
        $namespacedClass = new SomeNamespacedClassOne();

        $this->assertEquals(23, $globalClass->getValueOne());
        $this->assertEquals($return23, $globalClass->getValueTwo());
        $this->assertEquals(89, $globalClass->getValueThree());
        $this->assertEquals(23, $namespacedClass->getValueOne());
        $this->assertEquals($return23, $namespacedClass->getValueTwo());
        $this->assertEquals(89, $namespacedClass->getValueThree());
    }

    /**
     * It should reset instance method return between tests
     *
     * @test
     */
    public function should_reset_instance_method_return_between_tests(): void
    {
        $globalClass = new SomeGlobalClassOne();
        $namespacedClass = new SomeNamespacedClassOne();
        $this->assertEquals('original-value-one', $globalClass->getValueOne());
        $this->assertEquals('original-value-two', $globalClass->getValueTwo());
        $this->assertEquals('original-value-three', $globalClass->getValueThree());
        $this->assertEquals('original-value-one', $namespacedClass->getValueOne());
        $this->assertEquals('original-value-two', $namespacedClass->getValueTwo());
        $this->assertEquals('original-value-three', $namespacedClass->getValueThree());
    }

    /**
     * It should allow setting a static method return value
     *
     * @test
     */
    public function should_allow_setting_a_static_method_return_value(): void
    {
        $this->setMethodReturn(SomeGlobalClassOne::class, 'getStaticValueOne', 23);
        $return23 = function () {
            return 23;
        };
        $this->setMethodReturn(SomeGlobalClassOne::class, 'getStaticValueTwo', $return23);
        $this->setMethodReturn(SomeGlobalClassOne::class, 'getStaticValueThree', function () {
            return 89;
        }, true);
        $this->setMethodReturn(SomeNamespacedClassOne::class, 'getStaticValueOne', 23);
        $return23 = function () {
            return 23;
        };
        $this->setMethodReturn(SomeNamespacedClassOne::class, 'getStaticValueTwo', $return23);
        $this->setMethodReturn(SomeNamespacedClassOne::class, 'getStaticValueThree', function () {
            return 89;
        }, true);

        $this->assertEquals(23, SomeGlobalClassOne::getStaticValueOne());
        $this->assertEquals($return23, SomeGlobalClassOne::getStaticValueTwo());
        $this->assertEquals(89, SomeGlobalClassOne::getStaticValueThree());
        $this->assertEquals(23, SomeNamespacedClassOne::getStaticValueOne());
        $this->assertEquals($return23, SomeNamespacedClassOne::getStaticValueTwo());
        $this->assertEquals(89, SomeNamespacedClassOne::getStaticValueThree());
    }

    /**
     * It should reset static method return value between tests
     *
     * @test
     */
    public function should_reset_static_method_return_value_between_tests(): void
    {
        $this->assertEquals('original-static-value-one', SomeGlobalClassOne::getStaticValueOne());
        $this->assertEquals('original-static-value-two', SomeGlobalClassOne::getStaticValueTwo());
        $this->assertEquals('original-static-value-three', SomeGlobalClassOne::getStaticValueThree());
        $this->assertEquals('original-static-value-one', SomeNamespacedClassOne::getStaticValueOne());
        $this->assertEquals('original-static-value-two', SomeNamespacedClassOne::getStaticValueTwo());
        $this->assertEquals('original-static-value-three', SomeNamespacedClassOne::getStaticValueThree());
    }

    /**
     * It should allow unsetting a set method return value
     *
     * @test
     */
    public function should_allow_unsetting_a_set_method_return_value(): void
    {
        $this->setMethodReturn(SomeGlobalClassOne::class, 'getValueOne', 23);
        $this->setMethodReturn(SomeGlobalClassOne::class, 'getStaticValueOne', 23);
        $this->setMethodReturn(SomeNamespacedClassOne::class, 'getValueOne', 23);
        $this->setMethodReturn(SomeNamespacedClassOne::class, 'getStaticValueOne', 23);

        $this->assertEquals(23, (new SomeGlobalClassOne())->getValueOne());
        $this->assertEquals(23, SomeGlobalClassOne::getStaticValueOne());
        $this->assertEquals(23, (new SomeNamespacedClassOne())->getValueOne());
        $this->assertEquals(23, SomeNamespacedClassOne::getStaticValueOne());

        $this->unsetMethodReturn(SomeGlobalClassOne::class, 'getValueOne');
        $this->unsetMethodReturn(SomeGlobalClassOne::class, 'getStaticValueOne');
        $this->unsetMethodReturn(SomeNamespacedClassOne::class, 'getValueOne');
        $this->unsetMethodReturn(SomeNamespacedClassOne::class, 'getStaticValueOne');

        $this->assertEquals('original-value-one', (new SomeGlobalClassOne())->getValueOne());
        $this->assertEquals('original-static-value-one', SomeGlobalClassOne::getStaticValueOne());
        $this->assertEquals('original-value-one', (new SomeNamespacedClassOne())->getValueOne());
        $this->assertEquals('original-static-value-one', SomeNamespacedClassOne::getStaticValueOne());
    }

    /**
     * It should not throw when unsetting a non-set method return value
     *
     * @test
     */
    public function should_not_throw_when_unsetting_a_non_set_method_return_value(): void
    {
        $this->unsetMethodReturn(SomeGlobalClassOne::class, 'getValueOne');
        $this->unsetMethodReturn(SomeGlobalClassOne::class, 'getStaticValueOne');
        $this->unsetMethodReturn(SomeNamespacedClassOne::class, 'getValueOne');
        $this->unsetMethodReturn(SomeNamespacedClassOne::class, 'getStaticValueOne');
    }

    /**
     * It should handle method with reference arguments
     *
     * @test
     */
    public function should_handle_method_with_reference_arguments(): void
    {
        $this->setMethodReturn(SomeGlobalClassOne::class, 'modifyValueByReference', null);

        $input = [23, 89];
        $globalClass = new SomeGlobalClassOne();
        $globalClass->modifyValueByReference($input);
        $this->assertEquals([23, 89], $input);

        $this->setMethodReturn(SomeGlobalClassOne::class, 'modifyStaticValueByReference', null);

        $input = [23, 89];
        SomeGlobalClassOne::modifyStaticValueByReference($input);
        $this->assertEquals([23, 89], $input);

        $this->setMethodReturn(SomeNamespacedClassOne::class, 'modifyValueByReference', null);

        $input = [23, 89];
        $namespacedClass = new SomeNamespacedClassOne();
        $namespacedClass->modifyValueByReference($input);
        $this->assertEquals([23, 89], $input);

        $this->setMethodReturn(SomeNamespacedClassOne::class, 'modifyStaticValueByReference', null);

        $input = [23, 89];
        SomeNamespacedClassOne::modifyStaticValueByReference($input);
        $this->assertEquals([23, 89], $input);

        $this->setMethodReturn(SomeGlobalClassOne::class, 'modifyValueByReference', function (array &$input) {
            $input[] = 'hello';
        }, true);

        $input = [23, 89];
        $globalClass->modifyValueByReference($input);
        $this->assertEquals([23, 89, 'hello'], $input);

        $this->setMethodReturn(SomeGlobalClassOne::class, 'modifyStaticValueByReference', function (array &$input) {
            $input[] = 'hello';
        }, true);

        $input = [23, 89];
        SomeGlobalClassOne::modifyStaticValueByReference($input);
        $this->assertEquals([23, 89, 'hello'], $input);

        $this->setMethodReturn(SomeNamespacedClassOne::class, 'modifyValueByReference', function (array &$input) {
            $input[] = 'hello';
        }, true);

        $input = [23, 89];
        $namespacedClass->modifyValueByReference($input);
        $this->assertEquals([23, 89, 'hello'], $input);

        $this->setMethodReturn(SomeNamespacedClassOne::class, 'modifyStaticValueByReference', function (array &$input) {
            $input[] = 'hello';
        }, true);

        $input = [23, 89];
        SomeNamespacedClassOne::modifyStaticValueByReference($input);
        $this->assertEquals([23, 89, 'hello'], $input);
    }

    /**
     * It should allow setting a function hook
     *
     * @test
     */
    public function should_allow_setting_a_function_hook(): void
    {
        $headers = [];
        $hook = function (string $header, bool $replace = true, int $response_code = 0) use (
            &$headers
        ): void {
            $headers[] = [
                'header' => $header,
                'replace' => $replace,
                'response_code' => $response_code,
            ];
        };

        $headers = [];
        $this->setFunctionHook('header', $hook);

        header('Location: http://example.com', true, 301);
        header('X-Frame-Options: DENY', false, 200);

        $this->assertEquals([
            [
                'header' => 'Location: http://example.com',
                'replace' => true,
                'response_code' => 301,
            ],
            [
                'header' => 'X-Frame-Options: DENY',
                'replace' => false,
                'response_code' => 200,
            ],
        ], $headers);

        $headers = [];
        $this->setFunctionHook('someHeaderProxy', $hook);

        someHeaderProxy('X-REST-URL: http://example.com');

        $this->assertEquals([
            [
                'header' => 'X-REST-URL: http://example.com',
                'replace' => true,
                'response_code' => 0,
            ],
            [
                'header' => 'X-REST-URL: http://example.com',
                'replace' => true,
                'response_code' => 0,
            ],
        ], $headers);

        $headers = [];
        $this->setFunctionHook('lucatume\WPBrowser\Acme\Project\someHeaderProxy', $hook);

        \lucatume\WPBrowser\Acme\Project\someHeaderProxy('X-REST-URL: http://example.com');

        $this->assertEquals([
            [
                'header' => 'X-REST-URL: http://example.com',
                'replace' => true,
                'response_code' => 0,
            ],
            [
                'header' => 'X-REST-URL: http://example.com',
                'replace' => true,
                'response_code' => 0,
            ],
        ], $headers);
    }

    /**
     * It should allow unsetting a function hook
     *
     * @test
     */
    public function should_allow_unsetting_a_function_hook(): void
    {
        $headers = [];
        $hook = function (string $header, bool $replace = true, int $response_code = 0) use (
            &$headers
        ): void {
            $headers[] = [
                'header' => $header,
                'replace' => $replace,
                'response_code' => $response_code,
            ];
        };

        $this->setFunctionHook('header', $hook);
        $this->setFunctionHook('someHeaderProxy', $hook);
        $this->setFunctionHook('lucatume\WPBrowser\Acme\Project\someHeaderProxy', $hook);

        header('Location: http://example.com', true, 301);
        header('X-Frame-Options: DENY', false, 200);
        someHeaderProxy('X-REST-URL: http://example.com');
        \lucatume\WPBrowser\Acme\Project\someHeaderProxy('X-REST-URL: http://example.com');

        $this->assertCount(6, $headers);

        $this->unsetFunctionHook('header');
        $this->unsetFunctionHook('someHeaderProxy');
        $this->unsetFunctionHook('lucatume\WPBrowser\Acme\Project\someHeaderProxy');
        $headers = [];

        header('Location: http://example.com', true, 301);
        header('X-Frame-Options: DENY', false, 200);
        someHeaderProxy('X-REST-URL: http://example.com');
        \lucatume\WPBrowser\Acme\Project\someHeaderProxy('X-REST-URL: http://example.com');

        $this->assertCount(0, $headers);
    }

    /**
     * It should not throw when unsetting a not set function hook
     *
     * @test
     */
    public function should_not_throw_when_unsetting_a_not_set_function_hook(): void
    {
        $this->unsetFunctionHook('header');
        $this->unsetFunctionHook('someHeaderProxy');
        $this->unsetFunctionHook('lucatume\WPBrowser\Acme\Project\someHeaderProxy');
    }

    /**
     * It should allow setting a method hook
     *
     * @test
     */
    public function should_allow_setting_a_method_hook(): void
    {
        $headers = [];
        $hook = function (string $header, bool $replace = true, int $response_code = 0) use (
            &$headers
        ): void {
            $headers[] = [
                'header' => $header,
                'replace' => $replace,
                'response_code' => $response_code,
            ];
        };

        $this->setMethodHook(SomeGlobalClassOne::class, 'someHeaderProxy', $hook);
        $this->setMethodHook(SomeGlobalClassOne::class, 'someStaticHeaderProxy', $hook);
        $this->setMethodHook(SomeNamespacedClassOne::class, 'someHeaderProxy', $hook);
        $this->setMethodHook(SomeNamespacedClassOne::class, 'someStaticHeaderProxy', $hook);

        $globalClass = new SomeGlobalClassOne();
        $namespacedClass = new SomeNamespacedClassOne();

        $headers = [];
        $globalClass->someHeaderProxy('X-REST-URL: http://example.com');

        $this->assertEquals([
            [
                'header' => 'X-REST-URL: http://example.com',
                'replace' => true,
                'response_code' => 0,
            ]
        ], $headers);

        $headers = [];
        SomeGlobalClassOne::someStaticHeaderProxy('X-REST-URL: http://example.com');

        $this->assertEquals([
            [
                'header' => 'X-REST-URL: http://example.com',
                'replace' => true,
                'response_code' => 0,
            ]
        ], $headers);

        $headers = [];
        $namespacedClass->someHeaderProxy('X-REST-URL: http://example.com');

        $this->assertEquals([
            [
                'header' => 'X-REST-URL: http://example.com',
                'replace' => true,
                'response_code' => 0,
            ]
        ], $headers);

        $headers = [];
        SomeNamespacedClassOne::someStaticHeaderProxy('X-REST-URL: http://example.com');

        $this->assertEquals([
            [
                'header' => 'X-REST-URL: http://example.com',
                'replace' => true,
                'response_code' => 0,
            ]
        ], $headers);

        $headers = [];

        $this->unsetMethodHook(SomeGlobalClassOne::class, 'someHeaderProxy');
        $this->unsetMethodHook(SomeGlobalClassOne::class, 'someStaticHeaderProxy');
        $this->unsetMethodHook(SomeNamespacedClassOne::class, 'someHeaderProxy');
        $this->unsetMethodHook(SomeNamespacedClassOne::class, 'someStaticHeaderProxy');

        $globalClass->someHeaderProxy('X-REST-URL: http://example.com');
        SomeGlobalClassOne::someStaticHeaderProxy('X-REST-URL: http://example.com');
        $namespacedClass->someHeaderProxy('X-REST-URL: http://example.com');
        SomeNamespacedClassOne::someStaticHeaderProxy('X-REST-URL: http://example.com');

        $this->assertCount(0, $headers);
    }

    /**
     * It should not throw if unsetting a not set method hook
     *
     * @test
     */
    public function should_not_throw_if_unsetting_a_not_set_method_hook(): void
    {
        $this->unsetMethodHook(SomeGlobalClassOne::class, 'someHeaderProxy');
        $this->unsetMethodHook(SomeGlobalClassOne::class, 'someStaticHeaderProxy');
        $this->unsetMethodHook(SomeNamespacedClassOne::class, 'someHeaderProxy');
        $this->unsetMethodHook(SomeNamespacedClassOne::class, 'someStaticHeaderProxy');
    }

    /**
     * It should allow setting a constant
     *
     * @test
     */
    public function should_allow_setting_a_constant(): void
    {
        $this->setConstant('EXISTING_CONSTANT', 23);
        $this->setConstant('NOT_EXISTING_CONSTANT', 89);
        $this->setConstant('lucatume\WPBrowser\Acme\Project\EXISTING_CONSTANT', 89);
        $this->setConstant('lucatume\WPBrowser\Acme\Project\NOT_EXISTING_CONSTANT', 23);

        $this->assertEquals(23, EXISTING_CONSTANT);
        $this->assertEquals(89, \NOT_EXISTING_CONSTANT);
        $this->assertEquals(89, \lucatume\WPBrowser\Acme\Project\EXISTING_CONSTANT);
        $this->assertEquals(23, NOT_EXISTING_CONSTANT);

        $this->unsetConstant('EXISTING_CONSTANT');
        $this->unsetConstant('NOT_EXISTING_CONSTANT');
        $this->unsetConstant('lucatume\WPBrowser\Acme\Project\EXISTING_CONSTANT');
        $this->unsetConstant('lucatume\WPBrowser\Acme\Project\NOT_EXISTING_CONSTANT');

        $this->assertEquals('test-constant', EXISTING_CONSTANT);
        $this->assertFalse(defined('NOT_EXISTING_CONSTANT'));
        $this->assertEquals('test-constant', \lucatume\WPBrowser\Acme\Project\EXISTING_CONSTANT);
        $this->assertFalse(defined('\lucatume\WPBrowser\Acme\Project\NOT_EXISTING_CONSTANT'));
    }

    /**
     * It should allow setting a class constant
     *
     * @test
     */
    public function should_allow_setting_a_class_constant(): void
    {
        $this->setClassConstant('SomeGlobalClassOne', 'EXISTING_CONSTANT', 23);
        $this->setClassConstant('SomeGlobalClassOne', 'NOT_EXISTING_CONSTANT', 89);
        $this->setClassConstant('lucautme\WPBrowser\Acme\Project\SomeNamespacedClassOne', 'EXISTING_CONSTANT', 89);
        $this->setClassConstant('lucautme\WPBrowser\Acme\Project\SomeNamespacedClassOne', 'NOT_EXISTING_CONSTANT', 23);

        $this->assertEquals(23, SomeGlobalClassOne::EXISTING_CONSTANT);
        $this->assertEquals(89, SomeGlobalClassOne::NOT_EXISTING_CONSTANT);
        $this->assertEquals(89, SomeNamespacedClassOne::EXISTING_CONSTANT);
        $this->assertEquals(23, SomeNamespacedClassOne::NOT_EXISTING_CONSTANT);

        $this->unsetClassConstant('SomeGlobalClassOne', 'EXISTING_CONSTANT');
        $this->unsetClassConstant('SomeGlobalClassOne', 'NOT_EXISTING_CONSTANT');
        $this->unsetClassConstant('lucautme\WPBrowser\Acme\Project\SomeNamespacedClassOne', 'EXISTING_CONSTANT');
        $this->unsetClassConstant('lucautme\WPBrowser\Acme\Project\SomeNamespacedClassOne', 'NOT_EXISTING_CONSTANT');

        $this->assertEquals('test-constant', SomeGlobalClassOne::EXISTING_CONSTANT);
        $this->assertFalse(defined('SomeGlobalClassOne::NOT_EXISTING_CONSTANT'));
        $this->assertEquals(
            'test-constant',
            SomeNamespacedClassOne::EXISTING_CONSTANT
        );
        $this->assertFalse(defined('\lucautme\WPBrowser\Acme\Project\SomeNamespacedClassOne::NOT_EXISTING_CONSTANT'));
    }

    /**
     * It should allow setting a class mock to instance
     *
     * @test
     */
    public function should_allow_setting_a_class_mock_to_instance(): void
    {
        $mockSomeGlobalClassOne = new class extends SomeGlobalClassOne {
            public function getValueOne(): string
            {
                return 'mocked-value-one';
            }
        };
        $this->setClassMock(SomeGlobalClassOne::class, $mockSomeGlobalClassOne);

        $mockSomeGlobalClassOneInstanceOne = new SomeGlobalClassOne();
        $mockSomeGlobalClassOneInstanceTwo = new SomeGlobalClassOne();

        $this->assertSame($mockSomeGlobalClassOne, $mockSomeGlobalClassOneInstanceOne);
        $this->assertSame($mockSomeGlobalClassOne, $mockSomeGlobalClassOneInstanceTwo);
        $this->assertEquals('mocked-value-one', $mockSomeGlobalClassOneInstanceOne->getValueOne());
        $this->assertEquals('mocked-value-one', $mockSomeGlobalClassOneInstanceTwo->getValueOne());

        $mockSomeNamespacedClassOne = new class extends SomeNamespacedClassOne {
            public function getValueOne(): string
            {
                return 'mocked-value-one';
            }
        };
        $this->setClassMock(
            SomeNamespacedClassOne::class,
            $mockSomeNamespacedClassOne
        );

        $mockSomeNamespacedClassOneInstanceOne = new SomeNamespacedClassOne();
        $mockSomeNamespacedClassOneInstanceTwo = new SomeNamespacedClassOne();
        $this->assertEquals('mocked-value-one', $mockSomeNamespacedClassOneInstanceOne->getValueOne());
        $this->assertEquals('mocked-value-one', $mockSomeNamespacedClassOneInstanceTwo->getValueOne());

        $this->unsetClassMock(SomeGlobalClassOne::class);
        $this->unsetClassMock(SomeNamespacedClassOne::class);

        $this->assertNotSame($mockSomeGlobalClassOne, new SomeGlobalClassOne());
        $this->assertNotSame($mockSomeNamespacedClassOne, new SomeNamespacedClassOne());
    }

    /**
     * It should allow setting a class mock to a class
     *
     * @test
     */
    public function should_allow_setting_a_class_mock_to_a_class(): void
    {
        $this->setClassMock(SomeGlobalClassOne::class, SomeGlobalClassTwo::class);

        $mockSomeGlobalClassOneInstanceOne = new SomeGlobalClassOne();
        $mockSomeGlobalClassOneInstanceTwo = new SomeGlobalClassOne();

        $this->assertInstanceOf(SomeGlobalClassTwo::class, $mockSomeGlobalClassOneInstanceOne);
        $this->assertInstanceOf(SomeGlobalClassTwo::class, $mockSomeGlobalClassOneInstanceTwo);
        $this->assertNotSame($mockSomeGlobalClassOneInstanceOne, $mockSomeGlobalClassOneInstanceTwo);
        $this->assertEquals('another-value', $mockSomeGlobalClassOneInstanceOne->getValueOne());
        $this->assertEquals('another-value', $mockSomeGlobalClassOneInstanceTwo->getValueOne());

        $this->setClassMock(SomeNamespacedClassOne::class, SomeNamespacedClassTwo::class);

        $mockSomeNamespacedClassOneInstanceOne = new SomeNamespacedClassOne();
        $mockSomeNamespacedClassOneInstanceTwo = new SomeNamespacedClassOne();

        $this->assertInstanceOf(SomeNamespacedClassTwo::class, $mockSomeNamespacedClassOneInstanceOne);
        $this->assertInstanceOf(SomeNamespacedClassTwo::class, $mockSomeNamespacedClassOneInstanceTwo);
        $this->assertNotSame($mockSomeNamespacedClassOneInstanceOne, $mockSomeNamespacedClassOneInstanceTwo);
        $this->assertEquals('another-value', $mockSomeNamespacedClassOneInstanceOne->getValueOne());
        $this->assertEquals('another-value', $mockSomeNamespacedClassOneInstanceTwo->getValueOne());

        $this->unsetClassMock(SomeGlobalClassOne::class);
        $this->unsetClassMock(SomeNamespacedClassOne::class);

        $this->assertInstanceOf(SomeGlobalClassOne::class, new SomeGlobalClassOne());
        $this->assertInstanceOf(SomeNamespacedClassOne::class, new SomeNamespacedClassOne());
    }

    /**
     * It should not throw if trying to unset a not set class mock
     *
     * @test
     */
    public function should_not_throw_if_trying_to_unset_a_not_set_class_mock(): void
    {
        $this->unsetClassMock(SomeGlobalClassOne::class);
        $this->unsetClassMock(SomeNamespacedClassOne::class);
    }

    /**
     * It should allow unsetting a class final attribute
     *
     * @test
     */
    public function should_allow_unsetting_a_class_final_attribute(): void
    {
        $this->unsetClassFinalAttribute(SomeGlobalFinalClass::class);
        $this->unsetClassFinalAttribute(SomeNamespacedFinalClass::class);

        $globalExtension = new class extends SomeGlobalFinalClass {
            public function someMethod(): int
            {
                return 89;
            }
        };
        $this->assertEquals(89, $globalExtension->someMethod());

        $namespacedExtension = new class extends SomeNamespacedFinalClass {
            public function someMethod(): int
            {
                return 89;
            }
        };
        $this->assertEquals(89, $namespacedExtension->someMethod());

        $this->resetClassFinalAttribute(SomeGlobalFinalClass::class);
        $this->resetClassFinalAttribute(SomeNamespacedFinalClass::class);

        $this->assertTrue((new ReflectionClass(SomeGlobalFinalClass::class))->isFinal());
        $this->assertTrue((new ReflectionClass(SomeNamespacedFinalClass::class))->isFinal());
    }

    /**
     * It should not throw if trying to reset a not set class final attribute
     *
     * @test
     */
    public function should_not_throw_if_trying_to_reset_a_not_set_class_final_attribute(): void
    {
        $this->resetClassFinalAttribute(SomeGlobalFinalClass::class);
        $this->resetClassFinalAttribute(SomeNamespacedFinalClass::class);
    }

    /**
     * It should allow unsetting a class method final attribute
     *
     * @test
     */
    public function should_allow_unsetting_a_class_method_final_attribute(): void
    {
        $this->unsetMethodFinalAttribute(SomeGlobalClassWithFinalMethods::class, 'someFinalMethod');
        $this->unsetMethodFinalAttribute(SomeGlobalClassWithFinalMethods::class, 'someStaticFinalMethod');
        $this->unsetMethodFinalAttribute(SomeNamespacedClassWithFinalMethods::class, 'someFinalMethod');
        $this->unsetMethodFinalAttribute(SomeNamespacedClassWithFinalMethods::class, 'someStaticFinalMethod');

        $globalExtension = new class extends SomeGlobalClassWithFinalMethods {
            public function someFinalMethod(): int
            {
                return 123;
            }

            public static function someStaticFinalMethod(): int
            {
                return 189;
            }
        };

        $this->assertEquals(123, $globalExtension->someFinalMethod());
        $this->assertEquals(189, $globalExtension::someStaticFinalMethod());

        $namespacedExtension = new class extends SomeNamespacedClassWithFinalMethods {
            public function someFinalMethod(): int
            {
                return 91;
            }

            public static function someStaticFinalMethod(): int
            {
                return 66;
            }
        };

        $this->assertEquals(91, $namespacedExtension->someFinalMethod());
        $this->assertEquals(66, $namespacedExtension::someStaticFinalMethod());

        $this->resetMethodFinalAttribute(SomeGlobalClassWithFinalMethods::class, 'someFinalMethod');
        $this->resetMethodFinalAttribute(SomeGlobalClassWithFinalMethods::class, 'someStaticFinalMethod');
        $this->resetMethodFinalAttribute(SomeNamespacedClassWithFinalMethods::class, 'someFinalMethod');
        $this->resetMethodFinalAttribute(SomeNamespacedClassWithFinalMethods::class, 'someStaticFinalMethod');

        $this->assertTrue(
            (new ReflectionMethod(SomeGlobalClassWithFinalMethods::class, 'someFinalMethod'))->isFinal()
        );
        $this->assertTrue(
            (new ReflectionMethod(SomeGlobalClassWithFinalMethods::class, 'someStaticFinalMethod'))->isFinal()
        );
        $this->assertTrue(
            (new ReflectionMethod(SomeNamespacedClassWithFinalMethods::class, 'someFinalMethod'))->isFinal()
        );
        $this->assertTrue(
            (new ReflectionMethod(SomeNamespacedClassWithFinalMethods::class, 'someStaticFinalMethod'))->isFinal()
        );
    }

    /**
     * It should not throw if trying to reset a not unset method final attribute
     *
     * @test
     */
    public function should_not_throw_if_trying_to_reset_a_not_unset_method_final_attribute(): void
    {
        $this->resetMethodFinalAttribute(SomeGlobalClassWithFinalMethods::class, 'someFinalMethod');
        $this->resetMethodFinalAttribute(SomeGlobalClassWithFinalMethods::class, 'someStaticFinalMethod');
        $this->resetMethodFinalAttribute(SomeNamespacedClassWithFinalMethods::class, 'someFinalMethod');
        $this->resetMethodFinalAttribute(SomeNamespacedClassWithFinalMethods::class, 'someStaticFinalMethod');
    }

    /**
     * It should allow adding class methods
     *
     * @test
     */
    public function should_allow_adding_class_methods(): void
    {
        $this->addClassMethod(SomeGlobalClassWithoutMethods::class, 'instanceMethod', function (): int {
            return $this->number;
        });
        $this->addClassMethod(SomeGlobalClassWithoutMethods::class, 'staticMethod', function (): string {
            return self::$name;
        }, true);

        $this->assertEquals(23, (new SomeGlobalClassWithoutMethods())->instanceMethod());
        $this->assertEquals('Luca', SomeGlobalClassWithoutMethods::staticMethod());

        $this->removeClassMethod(SomeGlobalClassWithoutMethods::class, 'instanceMethod');
        $this->removeClassMethod(SomeGlobalClassWithoutMethods::class, 'staticMethod');

        $this->assertFalse(method_exists(SomeGlobalClassWithoutMethods::class, 'instanceMethod'));
        $this->assertFalse(method_exists(SomeGlobalClassWithoutMethods::class, 'staticMethod'));

        $this->addClassMethod(SomeNamespacedClassWithoutMethods::class, 'instanceMethod', function (): int {
            return $this->number;
        });
        $this->addClassMethod(SomeNamespacedClassWithoutMethods::class, 'staticMethod', function (): string {
            return self::$name;
        }, true);

        $this->assertEquals(23, (new SomeNamespacedClassWithoutMethods())->instanceMethod());
        $this->assertEquals('Luca', SomeNamespacedClassWithoutMethods::staticMethod());

        $this->removeClassMethod(SomeNamespacedClassWithoutMethods::class, 'instanceMethod');
        $this->removeClassMethod(SomeNamespacedClassWithoutMethods::class, 'staticMethod');

        $this->assertFalse(method_exists(SomeNamespacedClassWithoutMethods::class, 'instanceMethod'));
        $this->assertFalse(method_exists(SomeNamespacedClassWithoutMethods::class, 'staticMethod'));
    }

    /**
     * It should not throw if trying to remove not added class methods
     *
     * @test
     */
    public function should_not_throw_if_trying_to_remove_not_added_class_methods(): void
    {
        $this->removeClassMethod(SomeGlobalClassWithoutMethods::class, 'someNonExistingInstanceMethod');
        $this->removeClassMethod(SomeGlobalClassWithoutMethods::class, 'someNonExistingStaticMethod');
        $this->removeClassMethod(SomeNamespacedClassWithoutMethods::class, 'someNonExistingInstanceMethod');
        $this->removeClassMethod(SomeNamespacedClassWithoutMethods::class, 'someNonExistingStaticMethod');
    }

    /**
     * It should allow setting object properties
     *
     * @test
     */
    public function should_allow_setting_object_properties(): void
    {
        $globalClassInstance = new SomeGlobalClassWithoutMethods();
        $this->setObjectProperty($globalClassInstance, 'number', 89);
        $this->setObjectProperty(SomeGlobalClassWithoutMethods::class, 'name', 'Bob');

        $this->assertEquals(89, $this->getObjectProperty($globalClassInstance, 'number'));
        $this->assertEquals('Bob', $this->getObjectProperty(SomeGlobalClassWithoutMethods::class, 'name'));

        $this->resetObjectProperty($globalClassInstance, 'number');
        $this->resetObjectProperty(SomeGlobalClassWithoutMethods::class, 'name');
        $this->resetObjectProperty($globalClassInstance, 'someNonExistingInstanceProperty');
        $this->resetObjectProperty(SomeGlobalClassWithoutMethods::class, 'someNonExistingStaticProperty');

        $this->assertEquals(23, $this->getObjectProperty($globalClassInstance, 'number'));
        $this->assertEquals('Luca', $this->getObjectProperty(SomeGlobalClassWithoutMethods::class, 'name'));

        $namespacedClassInstance = new SomeNamespacedClassWithoutMethods();
        $this->setObjectProperty($namespacedClassInstance, 'number', 89);
        $this->setObjectProperty(SomeNamespacedClassWithoutMethods::class, 'name', 'Bob');

        $this->assertEquals(89, $this->getObjectProperty($namespacedClassInstance, 'number'));
        $this->assertEquals('Bob', $this->getObjectProperty(SomeNamespacedClassWithoutMethods::class, 'name'));

        $this->resetObjectProperty($namespacedClassInstance, 'number');
        $this->resetObjectProperty(SomeNamespacedClassWithoutMethods::class, 'name');
        $this->resetObjectProperty($namespacedClassInstance, 'someNonExistingInstanceProperty');
        $this->resetObjectProperty(SomeNamespacedClassWithoutMethods::class, 'someNonExistingStaticProperty');

        $this->assertEquals(23, $this->getObjectProperty($namespacedClassInstance, 'number'));
        $this->assertEquals('Luca', $this->getObjectProperty(SomeNamespacedClassWithoutMethods::class, 'name'));
    }

    /**
     * It should not throw if trying to reset not set object properties
     *
     * @test
     */
    public function should_not_throw_if_trying_to_reset_not_set_object_properties(): void
    {
        $globalClassInstance = new SomeGlobalClassWithoutMethods();
        $this->resetObjectProperty($globalClassInstance, 'someNonExistingInstanceProperty');
        $this->resetObjectProperty(SomeGlobalClassWithoutMethods::class, 'someNonExistingStaticProperty');

        $namespacedClassInstance = new SomeNamespacedClassWithoutMethods();
        $this->resetObjectProperty($namespacedClassInstance, 'someNonExistingInstanceProperty');
        $this->resetObjectProperty(SomeNamespacedClassWithoutMethods::class, 'someNonExistingStaticProperty');
    }

    /**
     * It should allow setting a method static variable
     *
     * @test
     */
    public function should_allow_setting_a_method_static_variable(): void
    {
        $someGlobalClassWithStaticVariablesInstance = new SomeGlobalClassWithStaticVariables();

        $this->setMethodStaticVariables(
            SomeGlobalClassWithStaticVariables::class,
            'theCounter',
            array_merge(
                $this->getMethodStaticVariables(SomeGlobalClassWithStaticVariables::class, 'theCounter'),
                ['counter' => 23]
            )
        );
        $this->setMethodStaticVariables(
            SomeGlobalClassWithStaticVariables::class,
            'theStaticCounter',
            array_merge(
                $this->getMethodStaticVariables(SomeGlobalClassWithStaticVariables::class, 'theStaticCounter'),
                ['counter' => 89]
            )
        );

        $this->assertEquals(23, $someGlobalClassWithStaticVariablesInstance->theCounter());
        $this->assertEquals(89, SomeGlobalClassWithStaticVariables::theStaticCounter());

        $this->resetMethodStaticVariables(SomeGlobalClassWithStaticVariables::class, 'theCounter');
        $this->resetMethodStaticVariables(SomeGlobalClassWithStaticVariables::class, 'theStaticCounter');

        $this->assertEquals(
            0,
            $this->getMethodStaticVariables(SomeGlobalClassWithStaticVariables::class, 'theCounter')['counter']
        );
        $this->assertEquals(
            0,
            $this->getMethodStaticVariables(SomeGlobalClassWithStaticVariables::class, 'theStaticCounter')['counter']
        );

        $namespacedClassWithStaticVariablesInstance = new NamespacedClassWithStaticVariables();

        $this->setMethodStaticVariables(
            NamespacedClassWithStaticVariables::class,
            'theCounter',
            array_merge(
                $this->getMethodStaticVariables(NamespacedClassWithStaticVariables::class, 'theCounter'),
                ['counter' => 23]
            )
        );
        $this->setMethodStaticVariables(
            NamespacedClassWithStaticVariables::class,
            'theStaticCounter',
            array_merge(
                $this->getMethodStaticVariables(NamespacedClassWithStaticVariables::class, 'theStaticCounter'),
                ['counter' => 89]
            )
        );

        $this->assertEquals(23, $namespacedClassWithStaticVariablesInstance->theCounter());
        $this->assertEquals(89, NamespacedClassWithStaticVariables::theStaticCounter());

        $this->resetMethodStaticVariables(NamespacedClassWithStaticVariables::class, 'theCounter');
        $this->resetMethodStaticVariables(NamespacedClassWithStaticVariables::class, 'theStaticCounter');

        $this->assertEquals(
            0,
            $this->getMethodStaticVariables(NamespacedClassWithStaticVariables::class, 'theCounter')['counter']
        );
        $this->assertEquals(
            0,
            $this->getMethodStaticVariables(NamespacedClassWithStaticVariables::class, 'theStaticCounter')['counter']
        );


        $this->setMethodStaticVariables(
            SomeGlobalClassWithStaticVariables::class,
            'theCounter',
            ['counter' => 23]
        );
        $this->setMethodStaticVariables(
            SomeGlobalClassWithStaticVariables::class,
            'theStaticCounter',
            ['counter' => 89]
        );
        $this->setMethodStaticVariables(
            NamespacedClassWithStaticVariables::class,
            'theCounter',
            ['counter' => 89, 'step' => 12]
        );
        $this->setMethodStaticVariables(
            NamespacedClassWithStaticVariables::class,
            'theStaticCounter',
            ['counter' => 14, 'step' => 13]
        );
    }

    /**
     * It should reset methods static variables between tests
     *
     * @test
     */
    public function should_reset_methods_static_variables_between_tests(): void
    {
        $this->assertEquals(
            ['counter' => 0, 'step' => 2],
            $this->getMethodStaticVariables(SomeGlobalClassWithStaticVariables::class, 'theCounter')
        );
        $this->assertEquals(
            ['counter' => 0, 'step' => 2],
            $this->getMethodStaticVariables(SomeGlobalClassWithStaticVariables::class, 'theStaticCounter')
        );
        $this->assertEquals(
            ['counter' => 0],
            $this->getMethodStaticVariables(NamespacedClassWithStaticVariables::class, 'theCounter')
        );
        $this->assertEquals(
            ['counter' => 0],
            $this->getMethodStaticVariables(NamespacedClassWithStaticVariables::class, 'theStaticCounter')
        );
    }

    /**
     * It should not throw if trying to reset a not set method static variable
     *
     * @test
     */
    public function should_not_throw_if_trying_to_reset_a_not_set_method_static_variable(): void
    {
        $this->resetMethodStaticVariables(SomeGlobalClassWithStaticVariables::class, 'theCounter');
        $this->resetMethodStaticVariables(SomeGlobalClassWithStaticVariables::class, 'theStaticCounter');
        $this->resetMethodStaticVariables(NamespacedClassWithStaticVariables::class, 'theCounter');
        $this->resetMethodStaticVariables(NamespacedClassWithStaticVariables::class, 'theStaticCounter');
    }

    /**
     * It should allow setting a function static variables
     *
     * @test
     */
    public function should_allow_setting_a_function_static_variables(): void
    {
        $this->assertEquals(
            ['counter' => 0, 'step' => 2],
            $this->getFunctionStaticVariables('withStaticVariable')
        );

        $this->setFunctionStaticVariables(
            'withStaticVariable',
            array_merge(
                $this->getFunctionStaticVariables('withStaticVariable'),
                ['counter' => 23]
            )
        );

        $this->assertEquals(23, withStaticVariable());

        $this->resetFunctionStaticVariables('withStaticVariable');

        $this->assertEquals(0, withStaticVariable());
        $this->assertEquals(['counter' => 2, 'step' => 2], $this->getFunctionStaticVariables('withStaticVariable'));

        $this->assertEquals(
            ['counter' => 0, 'step' => 2],
            $this->getFunctionStaticVariables('lucatume\WPBrowser\Acme\Project\withStaticVariable')
        );

        $this->setFunctionStaticVariables(
            'lucatume\WPBrowser\Acme\Project\withStaticVariable',
            array_merge(
                $this->getFunctionStaticVariables('lucatume\WPBrowser\Acme\Project\withStaticVariable'),
                ['counter' => 23]
            )
        );

        $this->assertEquals(23, \lucatume\WPBrowser\Acme\Project\withStaticVariable());

        $this->resetFunctionStaticVariables('lucatume\WPBrowser\Acme\Project\withStaticVariable');

        $this->assertEquals(0, \lucatume\WPBrowser\Acme\Project\withStaticVariable());
        $this->assertEquals(
            ['counter' => 2, 'step' => 2],
            $this->getFunctionStaticVariables('lucatume\WPBrowser\Acme\Project\withStaticVariable')
        );

        $this->setFunctionStaticVariables(
            'withStaticVariable',
            ['counter' => 89, 'step' => 3]
        );

        $this->setFunctionStaticVariables(
            'lucatume\WPBrowser\Acme\Project\withStaticVariable',
            ['counter' => 89, 'step' => 3]
        );
    }

    /**
     * It should reset function static variables between tests
     *
     * @test
     */
    public function should_reset_function_static_variables_between_tests(): void
    {
        $this->assertEquals(2, withStaticVariable());
        $this->assertEquals(2, \lucatume\WPBrowser\Acme\Project\withStaticVariable());
    }

    /**
     * It should not throw if trying to reset not set function static variable
     *
     * @test
     */
    public
    function should_not_throw_if_trying_to_reset_not_set_function_static_variable(): void
    {
        $this->resetFunctionStaticVariables('withStaticVariable');
        $this->resetFunctionStaticVariables('lucatume\WPBrowser\Acme\Project\withStaticVariable');
    }

    /**
     * It should allow adding and removing functions
     *
     * @test
     */
    public function should_allow_adding_and_removing_functions(): void
    {
        $this->assertFalse(function_exists('addTwentyThree'));

        $this->addFunction('addTwentyThree', function (int $number) {
            return $number + 23;
        });

        $this->assertTrue(function_exists('addTwentyThree'));
        $this->assertEquals(89, addTwentyThree(66));

        $this->removeFunction('addTwentyThree');

        $this->assertFalse(function_exists('addTwentyThree'));

        $this->addFunction('addTwentyThree', function (int $number) {
            return $number + 23;
        });

        $this->assertFalse(function_exists('Acme\Project\addTwentyThree'));

        $this->addFunction('Acme\Project\addTwentyThree', function (int $number) {
            return $number + 23;
        });

        $this->assertTrue(function_exists('Acme\Project\addTwentyThree'));
        $this->assertEquals(89, \Acme\Project\addTwentyThree(66));

        $this->removeFunction('Acme\Project\addTwentyThree');

        $this->assertFalse(function_exists('Acme\Project\addTwentyThree'));

        $this->addFunction('Acme\Project\addTwentyThree', function (int $number) {
            return $number + 23;
        });
    }

    /**
     * It should remove added functions between tests
     *
     * @test
     */
    public function should_remove_added_functions_between_tests(): void
    {
        $this->assertFalse(function_exists('addTwentyThree'));
        $this->assertFalse(function_exists('Acme\Project\addTwentyThree'));
    }

    /**
     * It should not throw if trying to remove a not added function
     *
     * @test
     */
    public function should_not_throw_if_trying_to_remove_a_not_added_function(): void
    {
        $this->removeFunction('addTwentyThree');
        $this->removeFunction('Acme\Project\addTwentyThree');
    }

    /**
     * It should allow preventing exit
     *
     * @test
     */
    public function should_allow_preventing_exit(): void
    {
        $this->preventExit();

        $this->assertEquals(1, ini_get('uopz.exit'));
        ob_start();
        echo "Print this and die\n";
        die();

        $this->assertEquals("Print this and die\n", ob_get_clean());
    }

    /**
     * It should not throw if trying to allow exit when exit not prevented
     *
     * @test
     */
    public function should_not_throw_if_trying_to_allow_exit_when_exit_not_prevented(): void
    {
        $this->allowExit();
    }

    /**
     * It should restore exit between tests
     *
     * @test
     */
    public function should_restore_exit_between_tests(): void
    {
        $this->assertEquals(1, ini_get('uopz.exit'));
    }
}
