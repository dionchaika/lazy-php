<?php

namespace Lazy\Db\Query\Compilers;

interface CompilerInterface
{
    /**
     * Compile an SQL for select query.
     *
     * @param  mixed  $table
     * @param  array  $columns
     * @param  array  $clauses
     * @param  bool  $distinct
     * @return string
     */
    public function compileSelect($table, array $columns, array $clauses, $distinct);
}
