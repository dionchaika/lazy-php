<?php

namespace Lazy\Db\Query;

/**
 * The query where clause builder class.
 */
class WhereClause
{
    /**
     * The array of query where clause parts.
     *
     * @var array
     */
    protected $parts = [];

    /**
     * equal...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function equal($column, $value)
    {

    }

    /**
     * or equal...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function orEqual($column, $value)
    {
        
    }
}
