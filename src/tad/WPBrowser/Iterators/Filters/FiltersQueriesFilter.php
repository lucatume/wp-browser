<?php

namespace tad\WPBrowser\Iterators\Filters;

use Iterator;

class FiltersQueriesFilter extends QueriesCallerBasedKeepingFilterIterator
{
    /**
     * ClassMethodQueriesFilter constructor.
     * @param Iterator $iterator
     * @param string $filter
     */
    public function __construct(Iterator $iterator, $filter)
    {
        parent::__construct($iterator);

        $this->needles = [
            "apply_filters('{$filter}')"
        ];
    }
}
