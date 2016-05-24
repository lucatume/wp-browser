<?php

namespace tad\WPBrowser\Iterators\Filters;


use Iterator;

class MainStatementQueriesFilter extends \FilterIterator
{
    /**
     * @var string
     */
    private $statement;

    /**
     * MainStatementQueriesFilter constructor.
     *
     * @param Iterator $iterator
     * @param string $statement
     */
    public function __construct(Iterator $iterator, $statement = 'SELECT')
    {
        parent::__construct($iterator);
        $this->statement = $statement;
    }

    /**
     * Check whether the current element of the iterator is acceptable
     * @link http://php.net/manual/en/filteriterator.accept.php
     * @return bool true if the current element is acceptable, otherwise false.
     * @since 5.1.0
     */
    public function accept()
    {
        $query = $this->getInnerIterator()->current();
        if (!preg_match('/^' . $this->statement . '/i', $query[0])) {
            return false;
        }

        return true;
    }
}