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
     * @param  string  $col1
     * @param  string  $op
     * @param  string|null  $col2
     * @return self
     */
    public function join(string $table, string $col1, string $op, ?string $col2 = null): self
    {
        return $this->addJoin('inner', $table, $col1, $op, $col2);
    }

    /**
     * inner join...
     *
     * An alias method name to join.
     *
     * @param  string  $table
     * @param  string  $col1
     * @param  string  $op
     * @param  string|null  $col2
     * @return self
     */
    public function innerJoin(string $table, string $col1, string $op, ?string $col2 = null): self
    {
        return $this->join($table, $col1, $op, $col2);
    }

    /**
     * left join...
     *
     * @param  string  $table
     * @param  string  $col1
     * @param  string  $op
     * @param  string|null  $col2
     * @return self
     */
    public function leftJoin(string $table, string $col1, string $op, ?string $col2 = null): self
    {
        return $this->addJoin('left', $table, $col1, $op, $col2);
    }

    /**
     * right join...
     *
     * @param  string  $table
     * @param  string  $col1
     * @param  string  $op
     * @param  string|null  $col2
     * @return self
     */
    public function rightJoin(string $table, string $col1, string $op, ?string $col2 = null): self
    {
        return $this->addJoin('right', $table, $col1, $op, $col2);
    }

    /**
     * full join...
     *
     * @param  string  $table
     * @param  string  $col1
     * @param  string  $op
     * @param  string|null  $col2
     * @return self
     */
    public function fullJoin(string $table, string $col1, string $op, ?string $col2 = null): self
    {
        return $this->addJoin('full', $table, $col1, $op, $col2);
    }

    /**
     * Add a query join clause.
     *
     * @param  string  $type
     * @param  string  $table
     * @param  string  $col1
     * @param  string  $op
     * @param  string|null  $col2
     * @return self
     */
    protected function addJoin(string $type, string $table, string $col1, string $op, ?string $col2 = null): self
    {
        $join['type'] = $type;

        [$table, $alias] = $this->devideAlias($table);

        $join['table'] = $table;
        if ($alias) {
            $this->aliases[$alias] = $table;
        }

        [$op, $col2] = [

            (null === $col2) ? '=' : $op,
            (null === $col2) ? $op : $col2

        ];

        $join['on'] = [

            'col1' => $col1,
            'op'   => $op,
            'col2' => $col2

        ];

        $this->joins[] = $join;

        return $this;
    }
}
