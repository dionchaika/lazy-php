<?php

namespace Lazy\Db\Query;

/**
 * @method mixed[] prepareOpAndVal($op, $val)
 */
trait HavingTrait
{
    /**
     * The array
     * of query having clauses.
     *
     * @var mixed[]
     */
    public $havings = [];

    /**
     * having...
     *
     * @param  string  $col
     * @param  mixed  $op
     * @param  mixed|null  $val
     * @param  string  $delim
     * @return \Lazy\Db\Query\Builder
     */
    public function having(string $col, $op, $val = null, string $delim = 'and'): Builder
    {
        [$op, $val] = $this->prepareOpAndVal($op, $val);

        $type = 'Simple';
        $this->havings[] = compact('type', 'col', 'op', 'val');

        return $this;
    }

    /**
     * or having...
     *
     * @param  string  $col
     * @param  mixed  $op
     * @param  mixed|null  $val
     * @return \Lazy\Db\Query\Builder
     */
    public function orHaving(string $col, $op, $val = null): Builder
    {
        return $this->having($col, $op, $val, 'or');
    }

    /**
     * having between...
     *
     * @param  string  $col
     * @param  mixed  $firstVal
     * @param  mixed  $secondVal
     * @param  string  $delim
     * @param  bool  $not
     * @return self
     */
    public function havingBetween(string $col, $firstVal, $secondVal, string $delim = 'and', bool $not = false): self
    {
        $type = $not ? 'NotBetween' : 'Between';
        $this->havings[] = compact('type', 'col', 'firstVal', 'secondVal', 'delim');

        return $this;
    }

    /**
     * or having between...
     *
     * @param  string  $col
     * @param  mixed  $firstVal
     * @param  mixed  $secondVal
     * @param  bool  $not
     * @return self
     */
    public function orHavingBetween(string $col, $firstVal, $secondVal, bool $not = false): self
    {
        return $this->havingBetween($col, $firstVal, $secondVal, 'or', $not);
    }

    /**
     * having not between...
     *
     * @param  string  $col
     * @param  mixed  $firstVal
     * @param  mixed  $secondVal
     * @param  string  $delim
     * @return self
     */
    public function havingNotBetween(string $col, $firstVal, $secondVal, string $delim = 'and'): self
    {
        return $this->havingBetween($col, $firstVal, $secondVal, $delim, true);
    }

    /**
     * or having not between...
     *
     * @param  string  $col
     * @param  mixed  $firstVal
     * @param  mixed  $secondVal
     * @return self
     */
    public function orHavingNotBetween(string $col, $firstVal, $secondVal): self
    {
        return $this->orHavingBetween($col, $firstVal, $secondVal, true);
    }
}
