<?php

namespace tad\WPBrowser\Iterators\Filters;

class SetupTearDownQueriesFilter extends QueriesCallerBasedFilterIterator
{

    protected $needles = [
        'Codeception\TestCase\WPTestCase->setUp',
        'Codeception\TestCase\WPTestCase->tearDown'
    ];
}
