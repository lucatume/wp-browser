<?php
/**
 * An implementation of the filter iterator to filter the setUp and tearDown methods.
 *
 * @package lucatume\WPBrowser\Iterators\Filters
 */

namespace lucatume\WPBrowser\Iterators\Filters;

/**
 * Class SetupTearDownQueriesFilter
 *
 * @package lucatume\WPBrowser\Iterators\Filters
 */
class SetupTearDownQueriesFilter extends QueriesCallerBasedFilterIterator
{

    /**
     * The list of setUp and tearDown methods.
     *
     * @var array<string>
     */
    protected array $needles = [
        'lucatume\WPBrowser\TestCase\WPTestCase->setUp',
        'lucatume\WPBrowser\TestCase\WPTestCase->tearDown'
    ];
}
