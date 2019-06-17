<?php

namespace Lazy\Db\Query;

interface CompilerInterface
{
    /**
     * Compile a query select statement.
     *
     * @param  \Lazy\Db\Query\Builder  $query
     * @return string
     */
    public function compileSelect(Builder $query): string;

    /**
     * Compile a query insert statement.
     *
     * @param  \Lazy\Db\Query\Builder  $query
     * @return string
     */
    public function compileInsert(Builder $query): string;

    /**
     * Compile a query update statement.
     *
     * @param  \Lazy\Db\Query\Builder  $query
     * @return string
     */
    public function compileUpdate(Builder $query): string;

    /**
     * Compile a query delete statement.
     *
     * @param  \Lazy\Db\Query\Builder  $query
     * @return string
     */
    public function compileDelete(Builder $query): string;
}
