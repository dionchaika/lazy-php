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

        $sql .= $this->compileCols($query);
        $sql .= $this->compileFrom($query);
        $sql .= $this->compileWhere($query);

        return $sql.';';
    }

    /**
     * Compile a query value.
     *
     * @param  mixed  $val
     * @return string
     */
    protected function compileVal($val): string
    {
        if (null === $val) {
            return 'null';
        }

        if (true === $val) {
            return 'true';
        }

        if (false === $val) {
            return 'false';
        }

        if (is_numeric($val)) {
            return (string) $val;
        }

        return '\''.addcslashes($val, '\'').'\'';
    }

    /**
     * Compile query columns.
     *
     * @param  \Lazy\Db\Query\Builder  $query
     * @return string
     */
    protected function compileCols(Builder $query): string
    {
        $cols = $query->cols;

        if (empty($cols)) {
            $cols[] = '*';
        }

        $sql = '';
        foreach ($cols as $col) {
            if ($col instanceof Raw) {
                $col = (string) $col;
            }

            $sql .= $col.', ';
        }

        return ' '.rtrim($sql, ', ');
    }

    /**
     * Compile a query from clause.
     *
     * @param  \Lazy\Db\Query\Builder  $query
     * @return string
     */
    protected function compileFrom(Builder $query): string
    {
        return ' from '.(($query->table instanceof Raw) ? (string) $query->table : $query->table);
    }
}
