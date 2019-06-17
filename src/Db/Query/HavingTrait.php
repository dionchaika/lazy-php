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
}
