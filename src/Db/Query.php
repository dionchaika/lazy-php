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
     * The current query type.
     *
     * Note: Read only property.
     *
     * @var int
     */
    protected $type = self::SELECT;

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
        'orderBy' => [],
        'limit'   => [],
        'offset'  => []

    ];

    /**
     * Get the current query type.
     *
     * Supported types:
     *      0 - "SELECT".
     *      1 - "INSERT".
     *      2 - "UPDATE".
     *      3 - "DELETE".
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

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
