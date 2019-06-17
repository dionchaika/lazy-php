<?php

namespace Lazy\Db\Query\Compilers;

use RuntimeException;
use Lazy\Db\Query\Raw;
use Lazy\Db\Query\Builder;
use Lazy\Db\Query\CompilerInterface;

/**
 * The query compiler base class.
 */
class Compiler implements CompilerInterface
{
    /**
     * {@inheritDoc}
     */
    public function compileSelect(Builder $query): string
    {
        if (! $query->table) {
            throw new RuntimeException('Invalid query! Table is not defined.');
        }

        $sql = $query->distinct ? 'select distinct' : 'select';

        $sql .= implode(' ', [

            $this->compileCols($query),
            $this->compileFrom($query),
            $this->compileWhere($query)

        ]).';';

        return $sql;
    }

    /**
     * Compile columns.
     *
     * @param  \Lazy\Db\Query\Builder  $query
     * @return string
     */
    protected function compileCols(Builder $query): string
    {

    }

    /**
     * Compile from.
     *
     * @param  \Lazy\Db\Query\Builder  $query
     * @return string
     */
    protected function compileFrom(Builder $query): string
    {

    }

    /**
     * Compile where.
     *
     * @param  \Lazy\Db\Query\Builder  $query
     * @return string
     */
    protected function compileWhere(Builder $query): string
    {

    }
}
