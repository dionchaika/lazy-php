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
        $col = '`'.str_replace('`', '\\`', $col).'`';

        if (null !== $table) {
            $col = '`'.str_replace('`', '\\`', $table).'`.'.$col;

            if (null !== $db) {
                $col = '`'.str_replace('`', '\\`', $db).'`.'.$col;
            }
        }

        return $col;
    }
}
