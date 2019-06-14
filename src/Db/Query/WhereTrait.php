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
            return $this->whereIsNot($col, null, $delim);
        }

        if ($val instanceof Closure) {
            return $this->whereSub($val, $delim, $not);
        }

        [$op, $val] = $this->prepareOpAndVal($op, $val);

        if ((null === $val || is_bool($val)) && '=' === $op) {
            return $this->whereIs($col, $val, $delim);
        }

        $type = 'simple';
        $this->wheres[] = compact('type', 'col', 'op', 'val', 'delim', 'not');

        return $this;
    }

    /**
     * or where...
     *
     * @param  \Closure|string  $col
     * @param  mixed|null  $op
     * @param  \Closure|mixed|null  $val
     * @param  bool  $not
     * @return self
     */
    public function orWhere($col, $op = null, $val = null, bool $not = false): self
    {
        return $this->where($col, $op, $val, 'or', $not);
    }

    /**
     * and where...
     *
     * @param  \Closure|string  $col
     * @param  mixed|null  $op
     * @param  \Closure|mixed|null  $val
     * @param  bool  $not
     * @return self
     */
    public function andWhere($col, $op = null, $val = null, bool $not = false): self
    {
        return $this->where($col, $op, $val, 'and', $not);
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
     * or where not...
     *
     * @param  string  $col
     * @param  mixed|null  $op
     * @param  mixed|null  $val
     * @return self
     */
    public function orWhereNot($col, $op = null, $val = null): self
    {
        return $this->orWhere($col, $op, $val, true);
    }

    /**
     * and where not...
     *
     * @param  string  $col
     * @param  mixed|null  $op
     * @param  mixed|null  $val
     * @return self
     */
    public function andWhereNot($col, $op = null, $val = null): self
    {
        return $this->andWhere($col, $op, $val, true);
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
     * or where is...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @param  bool  $not
     * @return self
     */
    public function orWhereIs(string $col, $val, $not = false): self
    {
        return $this->whereIs($col, $val, 'or', $not);
    }

    /**
     * and where is...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @param  bool  $not
     * @return self
     */
    public function andWhereIs(string $col, $val, $not = false): self
    {
        return $this->whereIs($col, $val, 'and', $not);
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
     * or where is not...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @return self
     */
    public function orWhereIsNot(string $col, $val): self
    {
        return $this->orWhereIs($col, $val, true);
    }

    /**
     * and where is not...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @return self
     */
    public function andWhereIsNot(string $col, $val): self
    {
        return $this->andWhereIs($col, $val, true);
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
        $sql =  $this->newBuilderForNestedWhere($closure)->getSql();

        $type = 'sub';
        $this->wheres[] = compact('type', 'sql', 'delim', 'not');

        return $this;
    }

    /**
     * or where ( select ... ) ...
     *
     * @param  \Closure  $closure
     * @param  bool  $not
     * @return self
     */
    public function orWhereSub(Closure $closure, bool $not = false): self
    {
        return $this->whereSub($closure, 'or', $not);
    }

    /**
     * and where ( select ... ) ...
     *
     * @param  \Closure  $closure
     * @param  bool  $not
     * @return self
     */
    public function andWhereSub(Closure $closure, bool $not = false): self
    {
        return $this->whereSub($closure, 'and', $not);
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
        $sql =  $this->newBuilderForNestedWhere($closure)->getSqlForWheres();

        $type = 'group';
        $this->wheres[] = compact('type', 'sql', 'delim', 'not');

        return $this;
    }

    /**
     * or where ( ... ) ...
     *
     * @param  \Closure  $closure
     * @param  bool  $not
     * @return self
     */
    public function orWhereGroup(Closure $closure, bool $not = false): self
    {
        return $this->whereGroup($closure, 'or', $not);
    }

    /**
     * and where ( ... ) ...
     *
     * @param  \Closure  $closure
     * @param  bool  $not
     * @return self
     */
    public function andWhereGroup(Closure $closure, bool $not = false): self
    {
        return $this->whereGroup($closure, 'and', $not);
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
     * Create a new query builder for a nested query where clause.
     *
     * @param  \Closure  $closure
     * @return \Lazy\Db\Query\Builder
     */
    protected function newBuilderForNestedWhere(Closure $closure): Builder
    {
        $closure($builder = new static($this->db, $this->table, $this->compiler));
        return $builder;
    }
}
