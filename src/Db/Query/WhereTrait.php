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

    /**
     * where (select ... ) ...
     *
     * @param  \Closure  $closure
     * @param  string  $delim
     * @param  bool  $not
     * @return self
     */
    public function whereSub(Closure $closure, string $delim = 'and', bool $not = false): self
    {
        return $this->addNestedWhere('sub', $closure, $delim, $not);
    }

    /**
     * where ( ... ) ...
     *
     * @param  \Closure  $closure
     * @param  string  $delim
     * @param  bool  $not
     * @return self
     */
    public function whereGroup(Closure $closure, string $delim = 'and', bool $not = false): self
    {
        return $this->addNestedWhere('group', $closure, $delim, $not);
    }

    /**
     * Add a nested query where clause.
     *
     * @param  string  $type
     * @param  \Closure  $closure
     * @param  string  $delim
     * @param  bool  $not
     * @return void
     */
    protected function addNestedWhere(string $type, Closure $closure, string $delim = 'and', bool $not = false)
    {
        $closure($builder = new static($this->db, $this->table, $this->compiler));

        $this->wheres[] = compact('type', 'builder', 'delim', 'not');

        return $this;
    }
}
