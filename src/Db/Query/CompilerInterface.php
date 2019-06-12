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
     * Compile a column.
     *
     * @param  string  $col
     * @param  string|null  $db
     * @param  string|null  $table
     * @return string
     */
    public function compileCol(string $col, ?string $db = null, ?string $table = null): string;
}
