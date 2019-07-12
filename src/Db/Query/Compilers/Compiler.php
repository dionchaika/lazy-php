<?php

namespace Lazy\Db\Query\Compilers;

/**
 * The base SQL compiler class.
 */
class Compiler implements CompilerInterface
{
    /**
     * {@inheritDoc}
     */
    public function compileSelect($table, array $columns, array $clauses, $distinct)
    {
        $select = $distinct ? 'select distinct' : 'select';

        if (empty($columns)) {
            $columns[] = '*';
        }

        $columns = implode(', ', $columns);

        return sprintf('%s %s from %s', $select, $columns, $table);
    }
}
