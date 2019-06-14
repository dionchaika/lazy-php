<?php

namespace Lazy\Db\Query;

use Closure;

trait WhereTrait
{
    /**
     * The array of query where clauses.
     *
     * @var mixed[]
     */
    protected $wheres = [];

    /**
     * where...
     *
     * @param  \Closure|string  $col
     * @param  mixed|null  $op
     * @param  mixed|null  $val
     * @param  string  $delim
     * @return self
     */
    public function where($col, $op = null, $val = null, string $delim = 'and'): self
    {
        
    }
}
