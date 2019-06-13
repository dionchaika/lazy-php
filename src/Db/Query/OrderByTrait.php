<?php

namespace Lazy\Db\Query;

trait OrderByTrait
{
    /**
     * The array of query order by clauses.
     *
     * @var mixed[]
     */
    protected $ordersBy = [];

    /**
     * order by...
     *
     * @param  string|string[]  $cols
     * @param  string  $order
     * @return self
     */
    public function orderBy($cols, $order = 'asc'): self
    {
        $this->ordersBy[] = [

            'cols'  => is_array($cols) ? $cols : [$cols],
            'order' => $order

        ];

        return $this;
    }

    /**
     * order by asc...
     *
     * @param  mixed  $cols
     * @return self
     */
    public function orderByAsc($cols): self
    {
        return $this->orderBy(is_array($cols) ? $cols : func_get_args(), 'asc');
    }

    /**
     * order by desc...
     *
     * @param  mixed  $cols
     * @return self
     */
    public function orderByDesc($cols): self
    {
        return $this->orderBy(is_array($cols) ? $cols : func_get_args(), 'desc');
    }
}
