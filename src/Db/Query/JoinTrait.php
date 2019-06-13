<?php

namespace Lazy\Db\Query;

/**
 * @property mixed[] $aliases
 * @method mixed[] devideAlias(string $name)
 */
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
     * @return self
     */
    public function join(string $table, string $firstCol, string $op, ?string $secondCol = null, string $type = 'inner'): self
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
     * @return self
     */
    public function innerJoin(string $table, string $firstCol, string $op, ?string $secondCol = null): self
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
     * @return self
     */
    public function leftJoin(string $table, string $firstCol, string $op, ?string $secondCol = null): self
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
     * @return self
     */
    public function rightJoin(string $table, string $firstCol, string $op, ?string $secondCol = null): self
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
     * @return self
     */
    public function fullJoin(string $table, string $firstCol, string $op, ?string $secondCol = null): self
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
     * @return self
     */
    protected function addJoin(string $table, string $firstCol, string $op, ?string $secondCol = null, string $type): self
    {
        [$table, $alias] = $this->devideAlias($table);

        if ($alias) {
            $this->aliases[$table] = $alias;
        }

        [$op, $secondCol] = (null === $secondCol) ? ['=', $op] : [$op, $secondCol];

        $this->joins[] = compact('table', 'firstCol', 'op', 'secondCol', 'type');

        return $this;
    }
}
