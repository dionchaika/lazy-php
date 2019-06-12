<?php

namespace Lazy\Db\Query\Compilers;

use Lazy\Db\Query\CompilerInterface;

class Compiler implements CompilerInterface
{
    /**
     * Compile value.
     *
     * @param  mixed  $val
     * @return string
     */
    public function compileVal($val): string
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
            return (string)$val;
        }

        return '\''.str_replace('\'', '\\\'', $val).'\'';
    }
}
