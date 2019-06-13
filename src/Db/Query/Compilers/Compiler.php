<?php

namespace Lazy\Db\Query\Compilers;

use Lazy\Db\Query\CompilerInterface;

/**
 * The base query compiler class.
 */
class Compiler implements CompilerInterface
{
    /**
     * {@inheritDoc}
     */
    public function compileSelect(
        ?string $db = null,
        string $table,
        array $cols,
        array $aliases,
        bool $distinct,
        array $joins,
        array $wheres,
        array $ordersBy
    ): string {
        if (empty($cols)) {
            $cols[] = '*';
        }

        $sql = $distinct ? 'select distinct' : 'select';

        $sql .= ' '.implode(', ', array_map(function ($col) use ($aliases) {
            return isset($aliases[$col]) ? $col.' as '.$aliases[$col] : $col;
        }, $cols));

        $sql .= ' from '.(isset($aliases[$table]) ? $table.' as '.$aliases[$table] : $table);

        if (!empty($ordersBy)) {
            $sql .= ' order by '.implode(', ', array_map(function ($orderBy) {
                return implode(', ', $orderBy['cols']).' '.$orderBy['order'];
            }, $ordersBy));
        }

        return $sql.';';
    }

    /**
     * {@inheritDoc}
     */
    public function compileDelete(?string $db = null, string $table, array $aliases, array $wheres): string
    {
        $sql = 'delete from '.(isset($aliases[$table]) ? $table.' as '.$aliases[$table] : $table);

        return $sql.';';
    }
}
