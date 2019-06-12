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
}
