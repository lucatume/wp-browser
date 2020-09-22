<?php
/**
 * Mocks the testing assertions provided by the phpspec Prophecy, but using Codeception Stubs.
 *
 * @example
 * ```php
 * <?php
 * class SomeTest extends Codeception\Test\Unit {
 *         use tad\WPBrowser\Traits\WithStubProphecy;
 *
 *         public function test_something_w_stub(){
 *              $mockUserKey = '123456';
 *              $service = $this->stubProphecy(Acme\Service::class)
 *                  ->fetchDataCount($mockUserKey)
 *                  ->willReturn(json_encode(['status' => 'success', 'count' => 23]));
 *
 *              $client = new Acme\Client($service->reveal());
 *
 *              $this->assertEquals(23, $client->getDataCount());
 *         }
 * }
 * ```
 *
 * @package tad\WPBrowser\StubProphecy
 */

namespace tad\WPBrowser\StubProphecy;

use Codeception\Stub;
use PHPUnit\Framework\TestCase;

/**
 * Class StubProphecy
 *
 * @package tad\WPBrowser\StubProphecy
 */
class StubProphecy
{
    /**
     * The fully qualified name of the class this stub prophecy was built for.
     * @var string
     */
    protected $class;

    /**
     * A list of all the method prophecies this instance will handle.
     * @var array<MethodProphecy>
     */
    protected $methodProphecies = [];

    /**
     * Whether the stub prophecy post conditions have been asserted or not.
     * @var bool
     */
    protected $assertedPostConditions = false;

    /**
     * The revealed stub prophecy, if previously revealed.
     *
     * @var self|object|null
     */
    protected $revealed;

    /**
     * The test case instance used by the stub prophecy.
     * @var TestCase
     */
    protected $testCase;

    /**
     * StubProphecy constructor.
     *
     * @param string   $class    The fully qualified name of the class to build a stub prophecy for.
     * @param TestCase $testCase The test case to attach the stubs to.
     */
    public function __construct($class, $testCase)
    {
        $this->class    = $class;
        $this->testCase = $testCase;
    }

    /**
     * Implements the magic method to allow fluent API to set up propecies.
     *
     * @param string       $name The name of the prophesized method.
     * @param array<mixed> $args The arguments passed to the function call, either a list of expected arguments,
     *                           or a callable that should be used to verify them.
     *
     * @return $this This object, for chaining purpose.
     *
     * @throws StubProphecyException If there's an issue reflecting on the the class method or the required argument
     *                               expectations are not met.
     */
    public function __call($name, array $args)
    {
        $this->methodProphecies[] = new MethodProphecy($this->class, $name, $args);

        return $this;
    }

    /**
     * Re-implementation of the phpspec Prophecy method that sets up a promise that the method just prophesized will
     * return the specified value or a closure that will return a value.
     *
     * @param mixed|null $returnValue The return value the prophesized method will return when called.
     *
     * @return $this This object, for chaining purpose.
     */
    public function willReturn($returnValue)
    {
        if ($returnValue instanceof Promise) {
            $returnValueCallback = $returnValue;
        } else {
            $returnValueCallback = static function () use ($returnValue) {
                return $returnValue;
            };
        }

        return $this->will($returnValueCallback);
    }

    /**
     * Reveals a stub prophecy building the actual stub and setting up call expectations and return values.
     *
     * @return object The stub object built by the \Codeception\Stub class.
     *
     * @throws \Exception|\RuntimeException If there's an issue building the stub object.
     *
     * @see \Codeception\Stub::make
     * @see \Codeception\Stub::makeEmpty
     */
    public function reveal()
    {
        if ($this->revealed !== null) {
            return $this->revealed;
        }

        $params = array_combine(
            array_map(function (MethodProphecy $methodProphecy) {
                return $methodProphecy->getName();
            }, $this->methodProphecies),
            array_map([ $this, 'buildProphecy' ], $this->methodProphecies)
        );

        if ($params === false) {
            throw new \RuntimeException('Failed to build the prophecy parameters.');
        }

        $this->revealed = Stub::makeEmpty($this->class, $params, $this->testCase);

        return $this->revealed;
    }

    /**
     * Builds a prophecy object based on the prophecy method call expectations and return values.
     *
     * @param MethodProphecy $methodProphecy The method prophecy to build the prophecy from.
     *
     * @return \Closure The closure that will assert the method expectations, if required, and build the return value.
     */
    protected function buildProphecy(MethodProphecy $methodProphecy)
    {
        return function () use ($methodProphecy) {
            $actualArgs = func_get_args();

            try {
                $matchingProphecy = $this->findMatchingMethodProphecy($methodProphecy->getName(), $actualArgs);
            } catch (\Exception $e) {
                throw $e;
            }

            return $matchingProphecy->buildReturnValue($actualArgs);
        };
    }

    /**
     * Sets the current method prophecy expected call count to 1.
     *
     * The method prophecy will fail if not called at all, in the test post-conditions, or if called more than once
     * during the test execution.
     *
     * @return $this The instance for chaining.
     */
    public function shouldBeCalledOnce()
    {
        $this->getCurrentMethodProphecy()->setExpectedCallCount(1);

        return $this;
    }

    /**
     * Returns the current prophecy being built by the class.
     *
     * @return MethodProphecy The current prophecy being built or `false` if no current method prophecy is being
     *                              built.
     *
     * @throws \RuntimeException If the current method prophecy is not set yet.
     */
    protected function getCurrentMethodProphecy()
    {
        $currentMethodProphecy = end($this->methodProphecies);

        if (empty($currentMethodProphecy)) {
            throw new \RuntimeException('No current method prophecy set.');
        }

        return $currentMethodProphecy;
    }

    /**
     * Sets the method call expectations to 0, the method should not be called.
     *
     * @return $this The instance for chaining.
     */
    public function shouldNotBeCalled()
    {
        $this->getCurrentMethodProphecy()->setExpectedCallCount(0);

        return $this;
    }

    /**
     * Sets an expectation that the current method prophecy should be called at least once.
     *
     * @return $this The current stub prophecy for chaining.
     */
    public function shouldBeCalled()
    {
        $this->getCurrentMethodProphecy()->shouldBeCalledAtLeastTimes(1);

        return $this;
    }

    /**
     * Asserts the stub prophecy post conditions.
     *
     * @return void
     */
    public function _assertPostConditions()
    {
        if ($this->assertedPostConditions === true) {
            return;
        }

        $this->assertedPostConditions = true;

        array_walk($this->methodProphecies, static function (MethodProphecy $methodProphecy) {
            $methodProphecy->assertPostConditions();
        });
    }

    /**
     * Returns a closure that will return the revealed prophecy itself as return value.
     *
     * @return Promise An invokable that will return the revealed prophecy itself as result of the call.
     */
    public function itself()
    {
        return new Promise([$this,'reveal']);
    }

    /**
     * Finds and returns a method prophecy matching the call and arguments.
     *
     * @param string       $name       The name of the called method .
     * @param array<mixed> $actualArgs An array of the actual call arguments.
     *
     * @return MethodProphecy Either a matching method prophecy or the first exception thrown while trying
     *                                   to find a matching method prophecy.
     * @throws \Exception On expectation error.
     */
    protected function findMatchingMethodProphecy($name, array $actualArgs)
    {
        $errors = [];
        /** @var MethodProphecy $candidateMethodProphecy */
        foreach ($this->methodProphecies as $candidateMethodProphecy) {
            if ($name !== $candidateMethodProphecy->getName()) {
                continue;
            }

            try {
                $candidateMethodProphecy->verifyExpectedCallCount();
                $candidateMethodProphecy->verifyArgsExpectation($actualArgs);

                return $candidateMethodProphecy;
            } catch (\Exception $e) {
                $errors[] = $e;
            }
        }

        throw $errors[0];
    }

    /**
     * Sets up a promise with a Closure.
     *
     * @param callable $callback The callback to call to process the input arguments or produce the return values.
     *
     * @return $this For chaining.
     */
    public function will(callable $callback)
    {
        $currentProphecy = $this->getCurrentMethodProphecy();
        $currentProphecy->setReturnValue($callback);

        return $this;
    }
}
