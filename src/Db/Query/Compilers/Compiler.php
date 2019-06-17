<?php

namespace Lazy\Db\Query\Compilers;

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
    public function compileSelect(Builder $builder): string
    {
        $cols = $builder->cols;
        if (empty($cols)) {
            $cols[] = '*';
        }

        $sql = $builder->distinct ? 'select distinct ' : 'select ';

        $sql .= $this->compileCols($builder, $cols);

        $sql .= ' from '.$builder->table;

        return $sql.';';
    }

    /**
     * Compile columns.
     *
     * @param  \Lazy\Db\Query\Builder  $builder
     * @param  string[]  $cols
     * @return string
     */
    protected function compileCols(Builder $builder, array $cols): string
    {
        return implode(', ', array_map(function ($col) use ($builder) {
            if ($col instanceof Raw) {
                return (string) $col;
            }

            if ($builder->db) {
                $col = $builder->db.'.'.$col;
            }

            return $col;
        }, $cols));
    }
}
