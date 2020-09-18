<?php
/**
 * An implementation of the filter iterator to filter the setUp and tearDown methods.
 *
 * @package tad\WPBrowser\Iterators\Filters
 */

namespace tad\WPBrowser\Iterators\Filters;

/**
 * Class SetupTearDownQueriesFilter
 *
 * @package tad\WPBrowser\Iterators\Filters
 */
class SetupTearDownQueriesFilter extends QueriesCallerBasedFilterIterator
{

    /**
     * The list of setUp and tearDown methods.
     *
     * @var array<string>
     */
    protected $needles = [
        'Codeception\TestCase\WPTestCase->setUp',
        'Codeception\TestCase\WPTestCase->tearDown'
    ];
}
