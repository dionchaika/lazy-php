<?php

namespace Lazy\Db\Query\Compilers;

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
    public function compileWhere(Builder $builder): string
    {
        
    }
}
