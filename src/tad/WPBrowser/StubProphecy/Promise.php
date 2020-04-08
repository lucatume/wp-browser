<?php
/**
 * A promise that will return the result of a callable when invoked.
 *
 * @package tad\WPBrowser\StubProphecy
 */

namespace tad\WPBrowser\StubProphecy;

/**
 * Class StubProphecyPromise
 *
 * @package tad\WPBrowser\StubProphecy
 */
class Promise
{
    /**
     * The callable that will produce the promise return value.
     * @var callable
     */
    private $callback;

    /**
     * StubProphecyPromise constructor.
     *
     * @param callable $callback The callback that will produce the promise return value.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Returns the value produced by the callback.
     *
     * @return mixed The callback return value.
     */
    public function __invoke()
    {
        return call_user_func($this->callback);
    }
}
