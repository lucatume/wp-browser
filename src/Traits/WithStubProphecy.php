<?php
/**
 * Provides methods to use a test and expectation API similar to the one of the phpspec/prophecy project, but based on
 * Codeception own mocking system.
 *
 * @package lucatume\WPBrowser\Traits
 */

namespace lucatume\WPBrowser\Traits;

use lucatume\WPBrowser\StubProphecy\StubProphecy;

/**
 * Class WithStubProphecy
 *
 * @package lucatume\WPBrowser\Traits
 */
trait WithStubProphecy
{
    /**
     * A list of the stub prophecies built by this object.
     * @var array<StubProphecy>
     */
    protected $stubProphecies = [];

    /**
     * Builds a prophecy for a class using Codeception Stubs.
     *
     * @param string $class The name of the class to prophesize.
     * @parama array<string,mixed> A map of methods that should be replaced with some preset return values or
     *                             callbacks.
     *
     * @return StubProphecy The built prophecy, as per phpspec Prophecy, call `reveal` to get a usable mock.
     */
    protected function stubProphecy($class, array $methodSet = [])
    {
        $stubProphecy = new StubProphecy($class, $this);
        $this->stubProphecies[] = $stubProphecy;
        $stubProphecy->bulkProphesizeMethods($methodSet);

        return $stubProphecy;
    }

    /**
     * Asserts the prophecies of each prophecy built as a test post-condition.
     *
     * @postCondition
     */
    public function _stubProphecyPostConditions()
    {
        array_walk($this->stubProphecies, static function (StubProphecy $stubProphecy) {
            $stubProphecy->_assertPostConditions();
        });
    }
}
