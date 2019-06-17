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
}
