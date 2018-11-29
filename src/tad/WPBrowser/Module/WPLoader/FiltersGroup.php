<?php

namespace tad\WPBrowser\Module\WPLoader;

class FiltersGroup
{
    /**
     * @var array
     */
    protected $filters;
    /**
     * @var callable|string
     */
    protected $removeCallback;
    /**
     * @var callable|string
     */
    protected $addCallback;

    /**
     * FiltersGroup constructor.
     *
     * @param FiltersGroup $toRemove
     */
    public function __construct(array $filters = [], callable $removeWith = null, callable $addWith = null)
    {
        $this->filters        = $filters;
        $this->removeCallback = null === $removeWith ? 'remove_filter' : $removeWith;
        $this->addCallback    = null === $addWith ? 'add_filter' : $addWith;
    }

    public function remove()
    {
        foreach ($this->filters as $filter) {
            $filterWithoutAcceptedArguments = array_slice($filter, 0, 3);
            call_user_func_array($this->removeCallback, $filterWithoutAcceptedArguments);
        }
    }

    public function add()
    {
        foreach ($this->filters as $filter) {
            call_user_func_array($this->addCallback, $filter);
        }
    }
}
