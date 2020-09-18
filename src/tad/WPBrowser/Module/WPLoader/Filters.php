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

    /**
     * The default filter priority.
     *
     * @var int
     */
    protected $defaultPriority = 10;

    /**
     * The callable that should be used to remove the filter.
     *
     * @var callable
     */
    protected $removeWith;

    /**
     * The callable that should be used to add the filter.
     *
     * @var callable
     */
    protected $addWith;

    /**
     * The default number of accepted arguments.
     *
     * @var int
     */
    protected $defaultAcceptedArguments = 1;

    /**
     * The list of filters to remove.
     *
     * @var array<array<mixed>>
     */
    protected $toRemove = [];

    /**
     * The list of filters to add.
     *
     * @var array<array<mixed>>
     */
    protected $toAdd = [];

    /**
     * Filters constructor.
     *
     * @param array<array<mixed>> $filters The filters to manage.
     */
    public function __construct(array $filters = [])
    {
        $this->toRemove = !empty($filters['remove'])
            ? array_map([$this, 'normalizeFilter'], $filters['remove'])
            : [];
        $this->toAdd = !empty($filters['add'])
            ? array_map([$this, 'normalizeFilter'], $filters['add'])
            : [];
    }

    /**
     * Formats and normalizes a list of filters.
     *
     * @param array<array<mixed>> $filters The list of filters to format.
     *
     * @return array<array<mixed>> The formatted list of filters.
     */
    public static function format(array $filters)
    {
        $instance = new self($filters);

        return $instance->toArray();
    }

    /**
     * Returns the current state in array format.
     *
     * @return array<array<array<mixed>>> A map of the filters to remove and to add.j:w
     */
    public function toArray()
    {
        return [
            'remove' => $this->toRemove,
            'add' => $this->toAdd,
        ];
    }

    /**
     * Returns the list of filters to remove.
     *
     * @return FiltersGroup The group of filters to remove.
     */
    public function toRemove()
    {
        return new FiltersGroup($this->toRemove, $this->removeWith, $this->addWith);
    }

    /**
     * Sets the callable that should be used to remove the filters.
     *
     * @param callable $removeWith The callable that should be used to remove the filters.
     *
     * @return void
     */
    public function removeWith(callable $removeWith)
    {
        $this->removeWith = $removeWith;
    }

    /**
     * Sets the callable that should be used to remove the filters.
     *
     * @param callable $addWith The callable that should be used to add the filters.
     *
     * @return void
     */
    public function addWith(callable $addWith)
    {
        $this->addWith = $addWith;
    }

    /**
     * Returns the list of filters to add.
     *
     * @return FiltersGroup The group of filters to add.
     */
    public function toAdd()
    {
        return new FiltersGroup($this->toAdd, $this->removeWith, $this->addWith);
    }

    /**
     * Normalizes a filter contents.
     *
     * @param array<mixed> $filter The current filter state.
     *
     * @return array<mixed> The normalized filter.
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
            $filter[] = $this->defaultAcceptedArguments;
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
            if (count($callbackFunc) !== 2
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
