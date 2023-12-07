<?php
/**
 * Models a group of WordPress filters to be added or removed together.
 *
 * @package lucatume\WPBrowser\Module\WPLoader
 */

namespace lucatume\WPBrowser\Module\WPLoader;

/**
 * Class FiltersGroup
 *
 * @package lucatume\WPBrowser\Module\WPLoader
 */
class FiltersGroup
{
    /**
     * @var array<array<mixed>>
     */
    protected $filters = [];
    /**
     * The callback that will be used to remove filters.
     *
     * @var callable
     */
    protected $removeCallback;

    /**
     * The callback that will be used to add filters.
     *
     * @var callable
     */
    protected $addCallback;

    /**
     * FiltersGroup constructor.
     *
     * @param array<array<mixed>> $filters    The list of filters to manage.
     * @param callable|null $removeWith       The callable that should be used to remove the filters or `null` to use
     *                                        the default one.
     * @param callable|null       $addWith    The callable that should be used to add the filters, or `null` to use the
     */
    public function __construct(array $filters = [],
        callable $removeWith = null,
        callable $addWith = null
    ) {
        /**
         * An array detailing each filter callback, priority and arguments.
         */
        $this->filters = $filters;
        $this->removeCallback = $removeWith ?? 'remove_filter';
        $this->addCallback    = $addWith ?? 'add_filter';
    }

    /**
     * Removes the filters of the group.
     */
    public function remove(): void
    {
        foreach ($this->filters as $filter) {
            $filterWithoutAcceptedArguments = array_slice($filter, 0, 3);
            call_user_func_array($this->removeCallback, $filterWithoutAcceptedArguments);
        }
    }

    /**
     * Adds the filters of the group.
     */
    public function add(): void
    {
        foreach ($this->filters as $filter) {
            call_user_func_array($this->addCallback, $filter);
        }
    }
}
