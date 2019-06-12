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

    /**
     * Compile a SELECT query.
     *
     * @param  mixed[]  $parts
     * @return string
     */
    public function compileSelect(array $parts): string;

    /**
     * Compile an INSERT query.
     *
     * @param  mixed[]  $parts
     * @return string
     */
    public function compileInsert(array $parts): string;

    /**
     * Compile an UPDATE query.
     *
     * @param  mixed[]  $parts
     * @return string
     */
    public function compileUpdate(array $parts): string;

    /**
     * Compile a DELETE query.
     *
     * @param  mixed[]  $parts
     * @return string
     */
    public function compileDelete(array $parts): string;
}
