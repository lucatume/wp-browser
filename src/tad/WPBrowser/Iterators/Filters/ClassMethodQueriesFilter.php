<?php

namespace tad\WPBrowser\Iterators\Filters;

use Iterator;

class ClassMethodQueriesFilter extends QueriesCallerBasedKeepingFilterIterator
{
    /**
     * ClassMethodQueriesFilter constructor.
     * @param Iterator $iterator
     * @param string $class
     * @param string $method
     */
    public function __construct(Iterator $iterator, $class, $method)
    {
        parent::__construct($iterator);

        $this->needles = [
            $class . '->' . $method,
            $class . '::' . $method
        ];
    }
}
