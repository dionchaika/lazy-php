<?php

namespace Lazy\Db\Query;

use Closure;

/**
 * @property string $db
 * @property string $table
 * @property \Lazy\Db\Query\CompilerInterface $compiler
 */
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
     * @param  \Closure|mixed|null  $val
     * @param  string  $delim
     * @param  bool  $not
     * @return self
     */
    public function where($col, $op = null, $val = null, string $delim = 'and', bool $not = false): self
    {
        if ($col instanceof Closure) {
            return $this->whereGroup($col, $delim, $not);
        }

        return $this;
    }
}
