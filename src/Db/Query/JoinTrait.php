<?php

namespace Lazy\Db\Query;

trait JoinTrait
{
    /**
     * The array of query join clauses.
     *
     * @var mixed[]
     */
    protected $joins = [];

    /**
     * join...
     *
     * @param  string  $table
     * @param  string  $col
     * @param  mixed  $op
     * @param  mixed|null  $val
     * @return self
     */
    public function join(string $table, string $col, $op, $val = null): self
    {
        return $this->addJoin('inner', $table, $col, $op, $val);
    }

    /**
     * left join...
     *
     * @param  string  $table
     * @param  string  $col
     * @param  mixed  $op
     * @param  mixed|null  $val
     * @return self
     */
    public function leftJoin(string $table, string $col, $op, $val = null): self
    {
        return $this->addJoin('left', $table, $col, $op, $val);
    }

    /**
     * right join...
     *
     * @param  string  $table
     * @param  string  $col
     * @param  mixed  $op
     * @param  mixed|null  $val
     * @return self
     */
    public function rightJoin(string $table, string $col, $op, $val = null): self
    {
        return $this->addJoin('right', $table, $col, $op, $val);
    }

    /**
     * full join...
     *
     * @param  string  $table
     * @param  string  $col
     * @param  mixed  $op
     * @param  mixed|null  $val
     * @return self
     */
    public function fullJoin(string $table, string $col, $op, $val = null): self
    {
        return $this->addJoin('full', $table, $col, $op, $val);
    }

    /**
     * Add a query join clause.
     *
     * @param  string  $type
     * @param  string  $table
     * @param  string  $col
     * @param  mixed  $op
     * @param  mixed|null  $val
     * @return self
     */
    protected function addJoin(string $type, string $table, string $col, $op, $val = null): self
    {
        $join['type'] = $type;
        $join['table'] = $table;

        [$op, $val] = [

            (null === $val) ? '=' : $op,
            (null === $val) ? $op : $val

        ];

        $join['on'] = [

            'col' => $col,
            'op'  => $op,
            'val' => $val

        ];

        $this->joins[] = $join;

        return $this;
    }
}
