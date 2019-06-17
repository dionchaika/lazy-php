<?php

namespace Lazy\Db\Query;

interface CompilerInterface
{
    /**
     * Compile a select statement.
     *
     * @param  \Lazy\Db\Query\Builder  $query
     * @return string
     */
    public function compileSelect(Builder $query): string;
}
