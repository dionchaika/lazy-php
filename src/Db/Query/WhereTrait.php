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
     * @return \Lazy\Db\Query\Builder
     */
    public function where($col, $op = null, $val = null, string $delim = 'and', bool $not = false): Builder
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
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhere($col, $op = null, $val = null, bool $not = false): Builder
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
     * @return \Lazy\Db\Query\Builder
     */
    public function andWhere($col, $op = null, $val = null, bool $not = false): Builder
    {
        return $this->where($col, $op, $val, 'and', $not);
    }

    /**
     * where not...
     *
     * @param  string  $col
     * @param  mixed|null  $op
     * @param  \Closure|mixed|null  $val
     * @param string  $delim
     * @return \Lazy\Db\Query\Builder
     */
    public function whereNot($col, $op = null, $val = null, string $delim = 'and'): Builder
    {
        return $this->where($col, $op, $val, $delim, true);
    }

    /**
     * or where not...
     *
     * @param  string  $col
     * @param  mixed|null  $op
     * @param  \Closure|mixed|null  $val
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereNot($col, $op = null, $val = null): Builder
    {
        return $this->orWhere($col, $op, $val, true);
    }

    /**
     * and where not...
     *
     * @param  string  $col
     * @param  mixed|null  $op
     * @param  \Closure|mixed|null  $val
     * @return \Lazy\Db\Query\Builder
     */
    public function andWhereNot($col, $op = null, $val = null): Builder
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
     * @return \Lazy\Db\Query\Builder
     */
    public function whereIs(string $col, $val, $delim = 'and', $not = false): Builder
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
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereIs(string $col, $val, $not = false): Builder
    {
        return $this->whereIs($col, $val, 'or', $not);
    }

    /**
     * and where is...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function andWhereIs(string $col, $val, $not = false): Builder
    {
        return $this->whereIs($col, $val, 'and', $not);
    }

    /**
     * where is not...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @param  string  $delim
     * @return \Lazy\Db\Query\Builder
     */
    public function whereIsNot(string $col, $val, $delim = 'and'): Builder
    {
        return $this->whereIs($col, $val, $delim, true);
    }

    /**
     * or where is not...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereIsNot(string $col, $val): Builder
    {
        return $this->orWhereIs($col, $val, true);
    }

    /**
     * and where is not...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @return \Lazy\Db\Query\Builder
     */
    public function andWhereIsNot(string $col, $val): Builder
    {
        return $this->andWhereIs($col, $val, true);
    }

    /**
     * where in...
     *
     * @param  string  $col
     * @param  \Closure|mixed[]  $vals
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function whereIn(string $col, $vals, string $delim = 'and', $not = false): Builder
    {
        if ($vals instanceof Closure) {
            $vals($builder = new static(null, null, $this->compiler));

            $type = 'in_sub';
            $this->wheres[] = compact('type', 'col', 'builder', 'delim', 'not');

            return $this;
        }

        $type = 'in';
        $this->wheres[] = compact('type', 'col', 'vals', 'delim', 'not');

        return $this;
    }

    /**
     * or where in...
     *
     * @param  string  $col
     * @param  \Closure|mixed[]  $vals
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereIn(string $col, $vals, $not = false): Builder
    {
        return $this->whereIn($col, $vals, 'or', $not);
    }

    /**
     * and where in...
     *
     * @param  string  $col
     * @param  \Closure|mixed[]  $vals
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function andWhereIn(string $col, $vals, $not = false): Builder
    {
        return $this->whereIn($col, $vals, 'and', $not);
    }

    /**
     * where not in...
     *
     * @param  string  $col
     * @param  \Closure|mixed[]  $vals
     * @param  string  $delim
     * @return \Lazy\Db\Query\Builder
     */
    public function whereNotIn(string $col, $vals, string $delim = 'and'): Builder
    {
        return $this->whereIn($col, $vals, $delim, true);
    }

    /**
     * or where not in...
     *
     * @param  string  $col
     * @param  \Closure|mixed[]  $vals
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereNotIn(string $col, $vals): Builder
    {
        return $this->whereIn($col, $vals, 'or', true);
    }

    /**
     * and where not in...
     *
     * @param  string  $col
     * @param  \Closure|mixed[]  $vals
     * @return \Lazy\Db\Query\Builder
     */
    public function andWhereNotIn(string $col, $vals): Builder
    {
        return $this->whereIn($col, $vals, 'and', true);
    }

    /**
     * where like...
     *
     * @param  string  $col
     * @param  \Closure|mixed  $val
     * @param  int|null  $criteria
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function whereLike(string $col, $val, ?int $criteria = null, string $delim = 'and', bool $not = false): Builder
    {
        if ($val instanceof Closure) {
            $val($builder = new static(null, null, $this->compiler));

            $type = 'like_sub';
            $this->wheres[] = compact('type', 'col', 'builder', 'criteria', 'delim', 'not');

            return $this;
        }

        $type = 'like';
        $this->wheres[] = compact('type', 'col', 'val', 'criteria', 'delim', 'not');

        return $this;
    }

    /**
     * or where like...
     *
     * @param  string  $col
     * @param  \Closure|mixed  $val
     * @param  int|null  $criteria
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereLike(string $col, $val, ?int $criteria = null, bool $not = false): Builder
    {
        return $this->whereLike($col, $val, $criteria, 'or', $not);
    }

    /**
     * and where like...
     *
     * @param  string  $col
     * @param  \Closure|mixed  $val
     * @param  int|null  $criteria
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function andWhereLike(string $col, $val, ?int $criteria = null, bool $not = false): Builder
    {
        return $this->whereLike($col, $val, $criteria, 'and', $not);
    }

    /**
     * where between...
     *
     * @param  string  $col
     * @param  \Closure|mixed  $firstVal
     * @param  \Closure|mixed  $secondVal
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function whereBetween(string $col, $firstVal, $secondVal, string $delim = 'and', bool $not = false): Builder
    {
        if ($firstVal instanceof Closure) {

        }
    }

    /**
     * where ( select ... ) ...
     *
     * @param  \Closure  $closure
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function whereSub(Closure $closure, string $delim = 'and', bool $not = false): Builder
    {
        $closure($builder = new static(null, null, $this->compiler));

        $sql = $builder->toSql();

        $type = 'sub';
        $this->wheres[] = compact('type', 'sql', 'delim', 'not');

        return $this;
    }

    /**
     * or where ( select ... ) ...
     *
     * @param  \Closure  $closure
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereSub(Closure $closure, bool $not = false): Builder
    {
        return $this->whereSub($closure, 'or', $not);
    }

    /**
     * and where ( select ... ) ...
     *
     * @param  \Closure  $closure
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function andWhereSub(Closure $closure, bool $not = false): Builder
    {
        return $this->whereSub($closure, 'and', $not);
    }

    /**
     * where ( ... ) ...
     *
     * @param  \Closure  $closure
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function whereGroup(Closure $closure, string $delim = 'and', bool $not = false): Builder
    {
        $closure($builder = new static($this->db, $this->table, $this->compiler));

        $wheres = $builder->wheres;

        $type = 'group';
        $this->wheres[] = compact('type', 'wheres', 'delim', 'not');

        return $this;
    }

    /**
     * or where ( ... ) ...
     *
     * @param  \Closure  $closure
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereGroup(Closure $closure, bool $not = false): Builder
    {
        return $this->whereGroup($closure, 'or', $not);
    }

    /**
     * and where ( ... ) ...
     *
     * @param  \Closure  $closure
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function andWhereGroup(Closure $closure, bool $not = false): Builder
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
}
