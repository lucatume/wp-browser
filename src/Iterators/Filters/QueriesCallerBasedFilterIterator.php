<?php
/**
 * A filter that will remove queries based on the caller they originated from.
 *
 * @package lucatume\WPBrowser\Iterators\Filters
 */

namespace lucatume\WPBrowser\Iterators\Filters;

/**
 * Class QueriesCallerBasedFilterIterator
 *
 * @package lucatume\WPBrowser\Iterators\Filters
 */
abstract class QueriesCallerBasedFilterIterator extends \FilterIterator
{
    /**
     * The list of callers to look for.
     *
     * @var array<string>
     */
    protected array $needles = [];

    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @link  http://php.net/manual/en/filteriterator.accept.php
     *
     * @return bool true if the current element is acceptable, otherwise false.
     */
    public function accept(): bool
    {
        /** @var array{0: string, 1: int, 2: string} $query */
        $query = $this->getInnerIterator()->current();
        foreach ($this->needles as $needle) {
            if (str_contains($query[2], $needle)) {
                return false;
            }
        }

        return true;
    }
}
