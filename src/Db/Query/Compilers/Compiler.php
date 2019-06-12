<?php

namespace Lazy\Db\Query\Compilers;

use Lazy\Db\Query\CompilerInterface;

class Compiler implements CompilerInterface
{
    /**
     * {@inheritDoc}
     */
    public function compileVal($val): string
    {
        if (null === $val) {
            return 'NULL';
        }

        if (true === $val) {
            return 'TRUE';
        }

        if (false === $val) {
            return 'FALSE';
        }

        if (is_numeric($val)) {
            return (string)$val;
        }

        return '\''.str_replace('\'', '\\\'', $val).'\'';
    }

    /**
     * {@inheritDoc}
     */
    public function compileCol(string $col, ?string $db = null, ?string $table = null): string
    {
        if (null !== $table) {
            $col = $table.'.'.$col;

            if (null !== $db) {
                $col = $db.'.'.$col;
            }
        }

        return $col;
    }

    /**
     * {@inheritDoc}
     */
    public function compileSelect(string $table, array $parts): string
    {
        $sql = $parts['distinct'] ? 'SELECT DISTINCT ' : 'SELECT ';

        if (empty($parts['select'])) {
            $parts['select'][] = '*';
        }

        $sql .= implode(', ', $parts['select']);

        $sql .= ' FROM '.$table;

        if (! empty($parts['where'])) {
            $sql .= ' WHERE '.implode(' ', $parts['where']);
        }

        if (! empty($parts['orderBy'])) {
            $sql .= ' ORDER BY '.implode(', ', $parts['orderBy']);
        }

        if (null !== $parts['limit']) {
            $sql .= ' LIMIT '.$parts['limit'];
        }

        return $sql.';';
    }
}
