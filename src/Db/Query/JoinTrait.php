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
     * @param  string  $firstCol
     * @param  string  $op
     * @param  string|null  $secondCol
     * @param  string  $type
     * @return \Lazy\Db\Query\Builder
     */
    public function join(string $table, string $firstCol, string $op, ?string $secondCol = null, string $type = 'inner'): Builder
    {
        return $this->addJoin($table, $firstCol, $op, $secondCol, $type);
    }

    /**
     * inner join...
     *
     * @param  string  $table
     * @param  string  $firstCol
     * @param  string  $op
     * @param  string|null  $secondCol
     * @return \Lazy\Db\Query\Builder
     */
    public function innerJoin(string $table, string $firstCol, string $op, ?string $secondCol = null): Builder
    {
        return $this->addJoin($table, $firstCol, $op, $secondCol, 'inner');
    }

    /**
     * left join...
     *
     * @param  string  $table
     * @param  string  $firstCol
     * @param  string  $op
     * @param  string|null  $secondCol
     * @return \Lazy\Db\Query\Builder
     */
    public function leftJoin(string $table, string $firstCol, string $op, ?string $secondCol = null): Builder
    {
        return $this->addJoin($table, $firstCol, $op, $secondCol, 'left');
    }

    /**
     * right join...
     *
     * @param  string  $table
     * @param  string  $firstCol
     * @param  string  $op
     * @param  string|null  $secondCol
     * @return \Lazy\Db\Query\Builder
     */
    public function rightJoin(string $table, string $firstCol, string $op, ?string $secondCol = null): Builder
    {
        return $this->addJoin($table, $firstCol, $op, $secondCol, 'right');
    }

    /**
     * full join...
     *
     * @param  string  $table
     * @param  string  $firstCol
     * @param  string  $op
     * @param  string|null  $secondCol
     * @return \Lazy\Db\Query\Builder
     */
    public function fullJoin(string $table, string $firstCol, string $op, ?string $secondCol = null): Builder
    {
        return $this->addJoin($table, $firstCol, $op, $secondCol, 'full');
    }

    /**
     * Add a query join clause.
     *
     * @param  string  $table
     * @param  string  $firstCol
     * @param  string  $op
     * @param  string|null  $secondCol
     * @return \Lazy\Db\Query\Builder
     */
    protected function addJoin(string $table, string $firstCol, string $op, ?string $secondCol = null, string $type): Builder
    {
        [$op, $secondCol] = (null === $secondCol) ? ['=', $op] : [$op, $secondCol];

        $this->joins[] = compact('table', 'firstCol', 'op', 'secondCol', 'type');

        return $this;
    }
}
