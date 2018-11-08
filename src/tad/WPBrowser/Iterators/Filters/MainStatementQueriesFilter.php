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
        $pattern = $this->isRegex($this->statement) ? $this->statement : '/^' . $this->statement . '/i';
        if (!preg_match($pattern, $query[0])) {
            return false;
        }

        return true;
    }

    private function isRegex($statement)
    {
        try {
            return preg_match($statement, null) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
