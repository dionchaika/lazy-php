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
     * @param  \Lazy\Db\Query\Builder|\Closure|string  $col
     * @param  mixed|null  $op
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed|null  $val
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
     * @param  \Lazy\Db\Query\Builder|\Closure|string  $col
     * @param  mixed|null  $op
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed|null  $val
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
     * @param  \Lazy\Db\Query\Builder|\Closure|string  $col
     * @param  mixed|null  $op
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed|null  $val
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
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed|null  $val
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
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed|null  $val
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
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed|null  $val
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
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed[]  $vals
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function whereIn(string $col, $vals, string $delim = 'and', $not = false): Builder
    {
        if ($vals instanceof Closure || $vals instanceof Builder) {
            $query = $this->getSubWhere($vals);
            $vals = [new Raw($query)];
        }

        $type = 'in';
        $this->wheres[] = compact('type', 'col', 'vals', 'delim', 'not');

        return $this;
    }

    /**
     * or where in...
     *
     * @param  string  $col
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed[]  $vals
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
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed[]  $vals
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
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed[]  $vals
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
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed[]  $vals
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
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed[]  $vals
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
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $val
     * @param  int|null  $criteria
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function whereLike(string $col, $val, ?int $criteria = null, string $delim = 'and', bool $not = false): Builder
    {
        if ($val instanceof Closure || $val instanceof Builder) {
            $query = $this->getSubWhere($val);
            $val = new Raw($query);
        }

        $type = 'like';
        $this->wheres[] = compact('type', 'col', 'val', 'criteria', 'delim', 'not');

        return $this;
    }

    /**
     * or where like...
     *
     * @param  string  $col
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $val
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
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $val
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
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $firstVal
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $secondVal
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function whereBetween(string $col, $firstVal, $secondVal, string $delim = 'and', bool $not = false): Builder
    {
        if ($firstVal instanceof Closure || $firstVal instanceof Builder) {
            $query = $this->getSubWhere($firstVal);
            $firstVal = new Raw($query);
        }

        if ($secondVal instanceof Closure || $secondVal instanceof Builder) {
            $query = $this->getSubWhere($secondVal);
            $secondVal = new Raw($query);
        }

        $type = 'between';
        $this->wheres[] = compact('type', 'col', 'firstVal', 'secondVal', 'delim', 'not');

        return $this;
    }

    /**
     * or where between...
     *
     * @param  string  $col
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $firstVal
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $secondVal
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereBetween(string $col, $firstVal, $secondVal, bool $not = false): Builder
    {
        return $this->whereBetween($col, $firstVal, $secondVal, 'or', $not);
    }

    /**
     * and where between...
     *
     * @param  string  $col
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $firstVal
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $secondVal
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function andWhereBetween(string $col, $firstVal, $secondVal, bool $not = false): Builder
    {
        return $this->whereBetween($col, $firstVal, $secondVal, 'and', $not);
    }

    /**
     * where not between...
     *
     * @param  string  $col
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $firstVal
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $secondVal
     * @param  string  $delim
     * @return \Lazy\Db\Query\Builder
     */
    public function whereNotBetween(string $col, $firstVal, $secondVal, string $delim = 'and'): Builder
    {
        return $this->whereBetween($col, $firstVal, $secondVal, $delim, true);
    }

    /**
     * or where not between...
     *
     * @param  string  $col
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $firstVal
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $secondVal
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereNotBetween(string $col, $firstVal, $secondVal): Builder
    {
        return $this->orWhereBetween($col, $firstVal, $secondVal, true);
    }

    /**
     * and where not between...
     *
     * @param  string  $col
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $firstVal
     * @param  \Lazy\Db\Query\Builder|\Closure|mixed  $secondVal
     * @return \Lazy\Db\Query\Builder
     */
    public function andWhereNotBetween(string $col, $firstVal, $secondVal): Builder
    {
        return $this->andWhereBetween($col, $firstVal, $secondVal, true);
    }

    /**
     * where ( select ... ) ...
     *
     * @param  \Lazy\Db\Query\Builder|\Closure  $callback
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function whereSub($callback, string $delim = 'and', bool $not = false): Builder
    {
        $query = $this->getSubWhere($callback);
        $query = new Raw($query);

        $type = 'sub';
        $this->wheres[] = compact('type', 'query', 'delim', 'not');

        return $this;
    }

    /**
     * or where ( select ... ) ...
     *
     * @param  \Lazy\Db\Query\Builder|\Closure  $callback
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereSub($callback, bool $not = false): Builder
    {
        return $this->whereSub($callback, 'or', $not);
    }

    /**
     * and where ( select ... ) ...
     *
     * @param  \Lazy\Db\Query\Builder|\Closure  $callback
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function andWhereSub($callback, bool $not = false): Builder
    {
        return $this->whereSub($callback, 'and', $not);
    }

    /**
     * where ( ... ) ...
     *
     * @param  \Lazy\Db\Query\Builder|\Closure  $callback
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function whereGroup($callback, string $delim = 'and', bool $not = false): Builder
    {
        $query = $this->getGroupWhere($callback);
        $wheres = $query->wheres;

        $type = 'group';
        $this->wheres[] = compact('type', 'wheres', 'delim', 'not');

        return $this;
    }

    /**
     * or where ( ... ) ...
     *
     * @param  \Lazy\Db\Query\Builder|\Closure  $callback
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereGroup($callback, bool $not = false): Builder
    {
        return $this->whereGroup($callback, 'or', $not);
    }

    /**
     * and where ( ... ) ...
     *
     * @param  \Lazy\Db\Query\Builder|\Closure  $callback
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function andWhereGroup($callback, bool $not = false): Builder
    {
        return $this->whereGroup($callback, 'and', $not);
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
     * Get the query sub where clause.
     *
     * @param  \Lazy\Db\Query\Builder|\Closure  $callback
     * @return \Lazy\Db\Query\Builder
     */
    protected function getSubWhere($callback): Builder
    {
        if ($callback instanceof Closure) {
            $callback($query = (new static(null, null, $this->compiler)));
            return $query;
        }

        return $callback;
    }

    /**
     * Get the query where clause group.
     *
     * @param  \Lazy\Db\Query\Builder|\Closure  $callback
     * @return \Lazy\Db\Query\Builder
     */
    protected function getGroupWhere($callback): Builder
    {
        if ($callback instanceof Closure) {
            $callback($query = (new static($this->db, $this->table, $this->compiler)));
            return $query;
        }

        return $callback;
    }
}
