<?php
/**
 * Provides methods to use a test and expectation API similar to the one of the phpspec/prophecy project, but based on
 * Codeception own mocking system.
 *
 * @package tad\WPBrowser\Traits
 */

namespace tad\WPBrowser\Traits;

use tad\WPBrowser\StubProphecy\StubProphecy;

/**
 * Class WithStubProphecy
 *
 * @package tad\WPBrowser\Traits
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
     *
     * @return StubProphecy The built prophecy, as per phpspec Prophecy, call `reveal` to get a usable mock.
     */
    protected function stubProphecy($class)
    {
        $stubProphecy = new StubProphecy($class, $this);
        $this->stubProphecies[] = $stubProphecy;

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
