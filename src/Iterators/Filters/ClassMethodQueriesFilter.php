<?php
/**
 * Keeps only quries originated by a class method call.
 *
 * @package lucatume\WPBrowser\Iterators\Filters
 */

namespace lucatume\WPBrowser\Iterators\Filters;

use Iterator;

/**
 * Class ClassMethodQueriesFilter
 *
 * @package lucatume\WPBrowser\Iterators\Filters
 */
class ClassMethodQueriesFilter extends QueriesCallerBasedKeepingFilterIterator
{
    /**
     * ClassMethodQueriesFilter constructor.
     *
     * @param Iterator<array{0: string, 1: float, 2: string, 3: float, 4?: array<int|string,mixed>}> $iterator
     * @param string $class              The class to filter queries by.
     * @param string $method             The class method to filter queries by.
     */
    public function __construct(Iterator $iterator, $class, string $method)
    {
        parent::__construct($iterator);

        $this->needles = [
            $class . '->' . $method,
            $class . '::' . $method
        ];
    }
}
