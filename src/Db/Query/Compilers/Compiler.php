<?php

namespace Lazy\Db\Query\Compilers;

use RuntimeException;
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

        $columns = $query->columns;

        if (empty($columns)) {
            $columns[] = '*';
        }

        $sql = $query->distinct ? 'select distinct' : 'select';

        return $sql.';';
    }

    /**
     * {@inheritDoc}
     */
    public function compileInsert(Builder $query): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function compileUpdate(Builder $query): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function compileDelete(Builder $query): string
    {
        return '';
    }
}
