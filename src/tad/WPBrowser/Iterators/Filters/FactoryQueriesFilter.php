<?php

namespace tad\WPBrowser\Iterators\Filters;

class FactoryQueriesFilter extends QueriesCallerBasedFilterIterator
{
    protected $needles = [
        'WP_UnitTest_Factory_For_Thing->create'
    ];
}
