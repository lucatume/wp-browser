<?php

namespace tad\WPBrowser\Iterators\Filters;

class QueriesCallerBasedKeepingFilterIterator extends \FilterIterator
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
            if (preg_match("/(?<!\\(')" . preg_quote($needle) . "(?!'\\))/", $query[2])) {
                return true;
            }
        }

        return false;
    }
}
