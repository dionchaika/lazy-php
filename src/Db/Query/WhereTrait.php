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
    public $wheres = [];

    /**
     * where (raw)...
     *
     * @param  mixed  $val
     * @param  string  $delim
     * @return \Lazy\Db\Query\Builder
     */
    public function whereRaw($val, string $delim = 'and'): Builder
    {
        $val = ($val instanceof Raw) ? $val : new Raw($val);

        $type = 'Raw';
        $this->wheres[] = compact('type', 'val', 'delim');

        return $this;
    }

    /**
     * or where (raw)...
     *
     * @param  mixed  $val
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereRaw($val): Builder
    {
        return $this->whereRaw($val, 'or');
    }

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
            return $this->whereSelect($col, $op, $val, $delim, $not);
        }

        [$op, $val] = $this->prepareOpAndVal($op, $val);

        if ((null === $val || is_bool($val)) && '=' === $op) {
            return $this->whereIs($col, $val);
        }

        $type = $not ? 'SimpleNot' : 'Simple';
        $this->wheres[] = compact('type', 'col', 'op', 'val', 'delim');

        return $this;
    }

    /**
     * or where...
     *
     * @param  mixed  $col
     * @param  mixed|null  $op
     * @param  mixed|null  $val
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhere($col, $op = null, $val = null, bool $not = false): Builder
    {
        return $this->where($col, $op, $val, 'or', $not);
    }

    /**
     * where not...
     *
     * @param  mixed  $col
     * @param  mixed|null  $op
     * @param  mixed|null  $val
     * @param  string  $delim
     * @return \Lazy\Db\Query\Builder
     */
    public function whereNot($col, $op = null, $val = null, string $delim = 'and'): Builder
    {
        return $this->where($col, $op, $val, $delim, true);
    }

    /**
     * or where not...
     *
     * @param  mixed  $col
     * @param  mixed|null  $op
     * @param  mixed|null  $val
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereNot($col, $op = null, $val = null): Builder
    {
        return $this->orWhere($col, $op, $val, true);
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
    public function whereIs(string $col, $val, string $delim = 'and', bool $not = false): Builder
    {
        $type = $not ? 'IsNot' : 'Is';
        $this->wheres[] = compact('type', 'col', 'val', 'delim');

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
    public function orWhereIs(string $col, $val, bool $not = false): Builder
    {
        return $this->whereIs($col, $val, 'or', $not);
    }

    /**
     * where is not...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @param  string  $delim
     * @return \Lazy\Db\Query\Builder
     */
    public function whereIsNot(string $col, $val, string $delim = 'and'): Builder
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

        $type = $not ? 'GroupNot' : 'Group';
        $this->wheres[] = compact('type', 'query', 'delim');

        return $this;
    }

    /**
     * or where ( ... ) ...
     *
     * @param  \Closure  $callback
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereGroup(Closure $callback, bool $not = false): Builder
    {
        return $this->whereGroup($callback, 'or', $not);
    }

    /**
     * where ( select ... ) ...
     *
     * @param  string  $col
     * @param  string  $op
     * @param  \Closure  $callback
     * @param  string  $delim
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function whereSelect(string $col, string $op, Closure $callback, string $delim = 'and', bool $not = false): Builder
    {
        $query = new static(null, null, $this->compiler);

        $callback($query);

        $type = $not ? 'SelectNot' : 'Select';
        $this->wheres[] = compact('type', 'col', 'op', 'query', 'delim');

        return $this;
    }

    /**
     * or where ( select ... ) ...
     *
     * @param  string  $col
     * @param  string  $op
     * @param  \Closure  $callback
     * @param  bool  $not
     * @return \Lazy\Db\Query\Builder
     */
    public function orWhereSelect(string $col, string $op, Closure $callback, bool $not = false): Builder
    {
        return $this->whereSelect($col, $op, $callback, 'or', $not);
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
