<?php

namespace tad\WPBrowser\Module\WPLoader;

use Codeception\Exception\ModuleException;

/**
 * Class Filters
 *
 * Handles operations on WordPress filters.
 *
 * @package tad\WPBrowser\Module\WPLoader
 */
class Filters
{

    protected $defaultPriority = 10;

    protected $removeWith;

    protected $addWith;

    protected $defultAcceptedArguments = 1;

    protected $toRemove = [];

    protected $toAdd = [];

    public function __construct(array $filters = [])
    {
        $this->toRemove = !empty($filters['remove'])
            ? array_map([$this, 'normalizeFilter'], $filters['remove'])
            : [];
        $this->toAdd = !empty($filters['add'])
            ? array_map([$this, 'normalizeFilter'], $filters['add'])
            : [];
    }

    public static function format(array $filters)
    {
        $instance = new static($filters);

        return $instance->toArray();
    }

    public function toArray()
    {
        return [
            'remove' => $this->toRemove,
            'add' => $this->toAdd,
        ];
    }

    public function toRemove()
    {
        return new FiltersGroup($this->toRemove, $this->removeWith, $this->addWith);
    }

    public function removeWith(callable $removeWith)
    {
        $this->removeWith = $removeWith;
    }

    public function addWith(callable $addWith)
    {
        $this->addWith = $addWith;
    }

    public function toAdd()
    {
        return new FiltersGroup($this->toAdd, $this->removeWith, $this->addWith);
    }

    /**
     * @param array $filter
     *
     * @return array
     *
     * @throws ModuleException If the filters information is not complete or not coherent.
     */
    protected function normalizeFilter(array $filter)
    {
        if (count($filter) < 2) {
            throw new ModuleException(
                __CLASS__,
                'Callback ' . json_encode($filter) . ' does not specify enough data for a filter: '
                . 'required at least tag and callback.'
            );
        }

        if (empty($filter[0]) || !is_string($filter[0])) {
            throw new ModuleException(__CLASS__, 'Callback ' . json_encode($filter) . ' does not specify a valid tag.');
        }

        if (count($filter) === 2) {
            $filter[] = $this->defaultPriority;
        }

        if (count($filter) === 3) {
            $filter[] = $this->defultAcceptedArguments;
        }

        if (count($filter) > 4) {
            throw new ModuleException(
                __CLASS__,
                'Callback ' . json_encode($filter) . ' contains too many arguments; '
                .'only tag, callback, priority and accepted arguments are supported'
            );
        }


        $callbackFunc = $filter[1];

        if (empty($callbackFunc) || !(is_string($callbackFunc) || is_array($callbackFunc))) {
            throw new ModuleException(
                __CLASS__,
                'Callback for ' . json_encode($filter) . ' is empty or the wrong type: '
                .'it should be a string (a function name) or an array of two strings (class name and a static method).'
            );
        }

        if (is_array($callbackFunc)) {
            if (!count($callbackFunc) === 2
                || count(array_filter($callbackFunc, 'is_string')) !== 2
            ) {
                throw new ModuleException(
                    __CLASS__,
                    'Callback for ' . json_encode($filter) . ' is weird: '
                    .'it should be a string (function name) or an array of two strings (class name and static method).'
                );
            }
        }

        return $filter;
    }
}
