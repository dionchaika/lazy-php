<?php

namespace Lazy\Db\Query\Compilers;

use Lazy\Db\Query\CompilerInterface;

class MySQLCompiler extends Compiler implements CompilerInterface
{
    /**
     * {@inheritDoc}
     */
    public function compileCol(string $col, ?string $db = null, ?string $table = null): string
    {
        if (preg_match('/^(\w+) (as) (\w+)$/i', $col, $matches)) {
            $col = '`'.str_replace('`', '\\`', $matches[1]).'`';
            $as = $matches[2];
            $alias = '`'.str_replace('`', '\\`', $matches[3]).'`';

            $col = $col.' '.$as.' '.$alias;
        } else {
            $col = '`'.str_replace('`', '\\`', $col).'`';
        }

        if (null !== $table) {
            $col = '`'.str_replace('`', '\\`', $table).'`.'.$col;

            if (null !== $db) {
                $col = '`'.str_replace('`', '\\`', $db).'`.'.$col;
            }
        }

        return $col;
    }
}
