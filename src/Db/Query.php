<?php

namespace Lazy\Db;

/**
 * The database query class.
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
     * Bind values to parameters in the query part.
     *
     * @param  string  $to
     * @param  array|mixed  $bindings
     * @return $this
     */
    public function bindValues($to = 'where', $bindings)
    {
        $this->bindings[$to] = array_merge(
            $this->bindings[$to], array ($bindings)
        );

        return $this;
    }
}
