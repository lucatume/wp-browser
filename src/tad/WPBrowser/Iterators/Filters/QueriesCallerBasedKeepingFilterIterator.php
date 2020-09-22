<?php
/**
 * A filter that will keep queries depending on the caller that originated them.
 *
 * @package tad\WPBrowser\Iterators\Filters
 */

namespace tad\WPBrowser\Iterators\Filters;

/**
 * Class QueriesCallerBasedKeepingFilterIterator
 *
 * @package tad\WPBrowser\Iterators\Filters
 */
class QueriesCallerBasedKeepingFilterIterator extends \FilterIterator
{

    /**
     * The list of elements to look for.
     *
     * @var array<string>
     */
    protected $needles = [];

    /**
     * Check whether the current element of the iterator is acceptable.
     *
     * @link  http://php.net/manual/en/filteriterator.accept.php
     *
     * @return bool True if the current element is acceptable, otherwise false.
     */
    public function accept()
    {
        $query = $this->getInnerIterator()->current();
        foreach ($this->needles as $needle) {
            if (preg_match("/(?<!\\(')" . preg_quote($needle) . "(?!'\\))/", $query[2])) {
                return true;
            }
        }

        return false;
    }
}
