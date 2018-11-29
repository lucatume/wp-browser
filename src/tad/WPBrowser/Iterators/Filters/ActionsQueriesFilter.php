<?php

namespace tad\WPBrowser\Iterators\Filters;

use Iterator;

class ActionsQueriesFilter extends QueriesCallerBasedKeepingFilterIterator
{
    /**
     * ClassMethodQueriesFilter constructor.
     * @param Iterator $iterator
     * @param string $action
     */
    public function __construct(Iterator $iterator, $action)
    {
        parent::__construct($iterator);

        $this->needles = [
            "do_action('{$action}')"
        ];
    }
}
