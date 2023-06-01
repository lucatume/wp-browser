<?php
/**
 * Filters queries by a filter.
 *
 * @package lucatume\WPBrowser\Iterators\Filters
 */

namespace lucatume\WPBrowser\Iterators\Filters;

use Iterator;

/**
 * Class FiltersQueriesFilter
 *
 * @package lucatume\WPBrowser\Iterators\Filters
 */
class FiltersQueriesFilter extends QueriesCallerBasedKeepingFilterIterator
{
    /**
     * ClassMethodQueriesFilter constructor.
     * @param Iterator<array{0: string, 1: float, 2: string, 3: float, 4?: array<int|string,mixed>}> $iterator
     * @param string $filter The filter handle to filter queries by.
     */
    public function __construct(Iterator $iterator, $filter)
    {
        parent::__construct($iterator);

        $this->needles = [
            "apply_filters('{$filter}')"
        ];
    }
}
