<?php

namespace Lazy\Db\Query;

trait OrderByTrait
{
    /**
     * The array of query order by clauses.
     *
     * @var mixed[]
     */
    public $ordersBy = [];

    /**
     * order by...
     *
     * @param  string|string[]  $cols
     * @param  string  $order
     * @return \Lazy\Db\Query\Builder
     */
    public function orderBy($cols, $order = 'asc'): Builder
    {
        $this->ordersBy[] = [

            'Cols'  => is_array($cols) ? $cols : [$cols],
            'Order' => $order

        ];

        return $this;
    }

    /**
     * order by asc...
     *
     * @param  mixed  $cols
     * @return \Lazy\Db\Query\Builder
     */
    public function orderByAsc($cols): Builder
    {
        return $this->orderBy(is_array($cols) ? $cols : func_get_args(), 'asc');
    }

    /**
     * order by desc...
     *
     * @param  mixed  $cols
     * @return \Lazy\Db\Query\Builder
     */
    public function orderByDesc($cols): Builder
    {
        return $this->orderBy(is_array($cols) ? $cols : func_get_args(), 'desc');
    }
}
