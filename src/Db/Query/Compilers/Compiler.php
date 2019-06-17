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
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function compileInsert(Builder $query): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function compileUpdate(Builder $query): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function compileDelete(Builder $query): string
    {
        return '';
    }
}
