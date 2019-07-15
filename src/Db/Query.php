<?php

namespace Lazy\Db;

use InvalidArgumentException;

/**
 * The query builder class.
 */
class Query
{
    /**
     * The query types.
     */
    const SELECT = 0;
    const INSERT = 1;
    const UPDATE = 2;
    const DELETE = 3;

    /**
     * The array of query value bindings.
     *
     * @var array
     */
    protected $bindings = [

        'select'  => [],
        'from'    => [],
        'join'    => [],
        'where'   => [],
        'groupBy' => [],
        'having'  => [],
        'orderBy' => []

    ];

    /**
     * Get the array of query value bindings.
     *
     * @param  string|null  $type
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getBindings($type = null)
    {
        if (! $type) {
            return $this->bindings;
        }

        if (array_key_exists($type, $this->bindings)) {
            return $this->bindings[$type];
        }

        throw new InvalidArgumentException("Invalid bindings type: {$type}!");
    }

    /**
     * Bind values to parameters.
     *
     * @param  mixed|array  $values
     * @param  string  $type
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function bindValues($values, $type = 'select')
    {
        if (array_key_exists($type, $this->bindings)) {
            $this->bindings[$type] = array_merge(
                $this->bindings[$type], (array) $values
            );

            return $this;
        }

        throw new InvalidArgumentException("Invalid bindings type: {$type}!");
    }
}
