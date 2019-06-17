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
     * @param  mixed  $col
     * @param  mixed|null  $op
     * @param  mixed|null  $val
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function where($col, $op = null, $val = null, string $delim = 'and', bool $not = false): Builder
    {
        if ($col instanceof Closure) {
            return $this->whereGroup($col, $delim, $not);
        }

        if (1 === func_num_args()) {
            return $this->whereIsNot($col, null);
        }

        if ($val instanceof Closure) {
            return $this->whereSelect($val, $delim, $not);
        }

        [$op, $val] = $this->prepareOpAndVal($op, $val);

        if ((null === $val || is_bool($val)) && '=' === $op) {
            return $this->whereIs($col, $val);
        }

        $type = 'Simple';
        $this->wheres[] = compact('type', 'col', 'op', 'val', 'delim', 'not');

        return $this;
    }

    /**
     * where ( ... ) ...
     *
     * @param  \Closure  $callback
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function whereGroup(Closure $callback, string $delim = 'and', bool $not = false): Builder
    {
        $query = new static($this->db, $this->table, $this->compiler);

        $callback($query);

        $wheres = $query->wheres;

        $type = 'Group';
        $this->wheres[] = compact('type', 'wheres', 'delim', 'not');

        return $this;
    }

    /**
     * where ( select ... ) ...
     *
     * @param  \Closure  $callback
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function whereSelect(Closure $callback, string $delim = 'and', bool $not = false): Builder
    {
        $query = new static(null, null, $this->compiler);

        $callback($query);

        $type = 'Group';
        $this->wheres[] = compact('type', 'query', 'delim', 'not');

        return $this;
    }

    /**
     * Prepare an operator and a value.
     *
     * @param  mixed  $op
     * @param  mixed  $val
     * @return mixed[]
     */
    protected function prepareOpAndVal($op, $val): array
    {
        return (null === $val) ? ['=', $op] : [$op, $val];
    }
}
