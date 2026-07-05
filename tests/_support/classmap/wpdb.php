<?php
/**
 * Minimal wpdb double for unit tests, autoloaded lazily so processes that
 * never touch it can still load the real WordPress class.
 */
class wpdb
{
    /** @var string */
    public $last_error = '';

    /** @var array<int,array<int,mixed>> */
    public $queries = [];

    /**
     * @return string[]
     */
    public function tables($scope = 'all', $prefix = true, $blog_id = 0): array
    {
        return [];
    }

    /**
     * @return int|bool
     */
    public function query($query)
    {
        return true;
    }
}
