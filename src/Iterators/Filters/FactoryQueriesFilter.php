<?php
/**
 * Filters out queries originated by the factories.
 *
 * @package lucatume\WPBrowser\Iterators\Filters
 */

namespace lucatume\WPBrowser\Iterators\Filters;

/**
 * Class FactoryQueriesFilter
 *
 * @package lucatume\WPBrowser\Iterators\Filters
 */
class FactoryQueriesFilter extends QueriesCallerBasedFilterIterator
{

    /**
     * The strings matching a factory query.
     *
     * @var array<string>
     */
    protected array $needles = [
        'WP_UnitTest_Factory_For_Thing->create'
    ];
}
