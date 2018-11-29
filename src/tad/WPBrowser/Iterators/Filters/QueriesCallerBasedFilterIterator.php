<?php

namespace tad\WPBrowser\Iterators\Filters;

abstract class QueriesCallerBasedFilterIterator extends \FilterIterator
{
    /**
     * @var array
     */
    protected $needles = [];

    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @link  http://php.net/manual/en/filteriterator.accept.php
     * @return bool true if the current element is acceptable, otherwise false.
     * @since 5.1.0
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
