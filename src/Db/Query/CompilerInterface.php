<?php

namespace Lazy\Db\Query;

interface CompilerInterface
{
    /**
     * Compile a value.
     *
     * @param  mixed  $val
     * @return string
     */
    public function compileVal($val): string;

    /**
     * Compile a wildcard.
     *
     * @param  mixed  $val
     * @param  int|null  $criteria
     * @return string
     */
    public function compileWildcard($val, ?int $criteria = null): string;
}
