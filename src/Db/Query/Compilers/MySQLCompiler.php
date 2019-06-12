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
            [$col, $as, $alias] = [

                $matches[1],
                $matches[2],
                $matches[3]

            ];

            $col = $this->quoteCol($col).' '.$as.' '.$this->quoteCol($alias);
        } else {
            $col = $this->quoteCol($col);
        }

        if (null !== $table) {
            $col = $this->quoteCol($table).'.'.$col;

            if (null !== $db) {
                $col = $this->quoteCol($db).'.'.$col;
            }
        }

        return $col;
    }

    /**
     * Quote a column.
     *
     * @param  string  $col
     * @return string
     */
    protected function quoteCol(string $col): string
    {
        return '`'.str_replace('`', '\\`', $col).'`';
    }
}
