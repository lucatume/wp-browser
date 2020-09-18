<?php
/**
 * Filters out queries originated by the factories.
 *
 * @package tad\WPBrowser\Iterators\Filters
 */

namespace tad\WPBrowser\Iterators\Filters;

/**
 * Class FactoryQueriesFilter
 *
 * @package tad\WPBrowser\Iterators\Filters
 */
class FactoryQueriesFilter extends QueriesCallerBasedFilterIterator
{

    /**
     * The strings matching a factory query.
     *
     * @var array<string>
     */
    protected $needles = [
        'WP_UnitTest_Factory_For_Thing->create'
    ];
}
