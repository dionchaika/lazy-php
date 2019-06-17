<?php

namespace Lazy\Db\Query;

use Closure;

/**
 * @property \Lazy\Db\Query\CompilerInterface $compiler
 */
trait JoinTrait
{
    /**
     * The array of query join clauses.
     *
     * @var mixed[]
     */
    public $joins = [];

    /**
     * join...
     *
     * @param  string  $joinedTable
     * @param  string  $firstCol
     * @param  mixed  $op
     * @param  mixed|null  $secondCol
     * @param  string  $type
     * @return \Lazy\Db\Query\Builder
     */
    public function join(string $joinedTable, string $firstCol, $op, $secondCol = null, string $type = 'inner'): Builder
    {
        return $this->addJoin($joinedTable, $firstCol, $op, $secondCol, $type);
    }

    /**
     * inner join...
     *
     * @param  string  $joinedTable
     * @param  string  $firstCol
     * @param  mixed  $op
     * @param  mixed|null  $secondCol
     * @return \Lazy\Db\Query\Builder
     */
    public function innerJoin(string $joinedTable, string $firstCol, $op, $secondCol = null): Builder
    {
        return $this->addJoin($joinedTable, $firstCol, $op, $secondCol, 'inner');
    }

    /**
     * left join...
     *
     * @param  string  $joinedTable
     * @param  string  $firstCol
     * @param  mixed  $op
     * @param  mixed|null  $secondCol
     * @return \Lazy\Db\Query\Builder
     */
    public function leftJoin(string $joinedTable, string $firstCol, $op, $secondCol = null): Builder
    {
        return $this->addJoin($joinedTable, $firstCol, $op, $secondCol, 'left');
    }

    /**
     * right join...
     *
     * @param  string  $joinedTable
     * @param  string  $firstCol
     * @param  mixed  $op
     * @param  mixed|null  $secondCol
     * @return \Lazy\Db\Query\Builder
     */
    public function rightJoin(string $joinedTable, string $firstCol, $op, $secondCol = null): Builder
    {
        return $this->addJoin($joinedTable, $firstCol, $op, $secondCol, 'right');
    }

    /**
     * full join...
     *
     * @param  string  $joinedTable
     * @param  string  $firstCol
     * @param  mixed  $op
     * @param  mixed|null  $secondCol
     * @return \Lazy\Db\Query\Builder
     */
    public function fullJoin(string $joinedTable, string $firstCol, $op, $secondCol = null): Builder
    {
        return $this->addJoin($joinedTable, $firstCol, $op, $secondCol, 'full');
    }

    /**
     * Add a query join clause.
     *
     * @param  string  $joinedTable
     * @param  string  $firstCol
     * @param  mixed  $op
     * @param  mixed|null  $secondCol
     * @param  string  $type
     * @return \Lazy\Db\Query\Builder
     */
    protected function addJoin(string $joinedTable, string $firstCol, $op, $secondCol = null, string $type): Builder
    {
        [$op, $secondCol] = $this->prepareOpAndSecondCol($op, $secondCol);

        if ($secondCol instanceof Closure) {
            $query = new static(null, null, $this->compiler);

            $secondCol($query);

            $this->joins[] = compact('type', 'joinedTable', 'firstCol', 'op', 'query');
        } else {
            $this->joins[] = compact('type', 'joinedTable', 'firstCol', 'op', 'secondCol');
        }

        return $this;
    }

    /**
     * Prepare an operation and a second column.
     *
     * @param  mixed  $op
     * @param  mixed  $secondCol
     * @return mixed[]
     */
    protected function prepareOpAndSecondCol($op, $secondCol): array
    {
        return (null === $secondCol) ? ['=', $op] : [$op, $secondCol];
    }
}
