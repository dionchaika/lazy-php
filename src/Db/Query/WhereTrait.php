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

        if (1 === func_num_args()) {
            return $this->whereIs($col, null, $delim, true);
        }

        if ($val instanceof Closure) {
            return $this->whereSub($val, $delim, $not);
        }

        [$op, $val] = $this->prepareOpAndVal($op, $val);

        if ((null === $val || is_bool($val)) && '=' === $op) {
            return $this->whereIs($col, $val, $delim, false);
        }

        $type = 'simple';
        $this->wheres[] = compact('type', 'col', 'op', 'val', 'delim', 'not');

        return $this;
    }

    /**
     * where not...
     *
     * @param  string  $col
     * @param  mixed|null  $op
     * @param  mixed|null  $val
     * @param string  $delim
     * @return self
     */
    public function whereNot($col, $op = null, $val = null, string $delim = 'and'): self
    {
        return $this->where($col, $op, $val, $delim, true);
    }

    /**
     * where is...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @param  string  $delim
     * @param  bool  $not
     * @return self
     */
    public function whereIs(string $col, $val, $delim = 'and', $not = false): self
    {
        $type = 'is';
        $this->wheres[] = compact('type', 'col', 'val', 'delim', 'not');

        return $this;
    }

    /**
     * where is not...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @param  string  $delim
     * @return self
     */
    public function whereIsNot(string $col, $val, $delim = 'and'): self
    {
        return $this->whereIs($col, $val, $delim, true);
    }

    /**
     * where in...
     *
     * @param  string  $col
     * @param  \Closure|mixed  $vals
     * @param  string  $delim
     * @param  bool  $not
     * @return self
     */
    public function whereIn(string $col, $vals, string $delim = 'and', bool $not = false): self
    {
        if ($vals instanceof Closure) {
            $vals[] = ($this->getNestedWhere($vals))->toSql();
        }

        $type = 'in';
        $this->wheres[] = compact('type', 'cols', 'vals', 'delim', 'not');

        return $this;
    }

    /**
     * where in...
     *
     * @param  string  $col
     * @param  \Closure|mixed  $vals
     * @param  string  $delim
     * @return self
     */
    public function whereNotIn(string $col, $vals, string $delim = 'and'): self
    {
        return $this->whereIn($col, $vals, $delim, true);
    }

    /**
     * where ( select ... ) ...
     *
     * @param  \Closure  $closure
     * @param  string  $delim
     * @param  bool  $not
     * @return self
     */
    public function whereSub(Closure $closure, string $delim = 'and', bool $not = false): self
    {
        $builder =  $this->getNestedWhere($closure);

        $type = 'sub';
        $this->wheres[] = compact('type', 'builder', 'delim', 'not');

        return $this;
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
        $builder =  $this->getNestedWhere($closure);

        $type = 'group';
        $this->wheres[] = compact('type', 'builder', 'delim', 'not');

        return $this;
    }

    /**
     * Get the array of query where clauses.
     *
     * @return mixed[]
     */
    protected function getWheres(): array
    {
        return $this->wheres;
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

    /**
     * Get the nested query where clause.
     *
     * @param  \Closure  $closure
     * @return \Lazy\Db\Query\Builder
     */
    protected function getNestedWhere(Closure $closure): Builder
    {
        $closure($builder = new static($this->db, $this->table, $this->compiler));
        return $builder;
    }
}
