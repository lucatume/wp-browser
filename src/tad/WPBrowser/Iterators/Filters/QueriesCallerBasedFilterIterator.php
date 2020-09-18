<?php
/**
 * A filter that will remove queries based on the caller they originated from.
 *
 * @package tad\WPBrowser\Iterators\Filters
 */

namespace tad\WPBrowser\Iterators\Filters;

/**
 * Class QueriesCallerBasedFilterIterator
 *
 * @package tad\WPBrowser\Iterators\Filters
 */
abstract class QueriesCallerBasedFilterIterator extends \FilterIterator
{
    /**
     * The list of callers to look for.
     *
     * @var array<string>
     */
    protected $needles = [];

    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @link  http://php.net/manual/en/filteriterator.accept.php
     *
     * @return bool true if the current element is acceptable, otherwise false.
     */
    public function accept()
    {
        $query = $this->getInnerIterator()->current();
        foreach ($this->needles as $needle) {
            if (strpos($query[2], $needle) !== false) {
                return false;
            }
        }

        return true;
    }
}
