<?php

namespace tad\WPBrowser\Iterators\Filters;

use Iterator;

class FunctionQueriesFilter extends QueriesCallerBasedKeepingFilterIterator
{
    /**
     * ClassMethodQueriesFilter constructor.
     * @param Iterator $iterator
     * @param string $function
     */
    public function __construct(Iterator $iterator, $function)
    {
        parent::__construct($iterator);

        $this->needles = [
            $function
        ];
    }
}
