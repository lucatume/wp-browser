<?php
/**
 * Filters queries by a filter.
 *
 * @package tad\WPBrowser\Iterators\Filters
 */

namespace tad\WPBrowser\Iterators\Filters;

use Iterator;

/**
 * Class FiltersQueriesFilter
 *
 * @package tad\WPBrowser\Iterators\Filters
 */
class FiltersQueriesFilter extends QueriesCallerBasedKeepingFilterIterator
{
    /**
     * ClassMethodQueriesFilter constructor.
     * @param Iterator<string> $iterator The iterator to filter.
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
