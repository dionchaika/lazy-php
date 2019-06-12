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
     * @param  string  $table
     * @param  mixed[]  $parts
     * @return string
     */
    public function compileSelect(string $table, array $parts): string;

    /**
     * Compile an INSERT query.
     *
     * @param  string  $table
     * @param  mixed[]  $parts
     * @return string
     */
    public function compileInsert(string $table, array $parts): string;

    /**
     * Compile an UPDATE query.
     *
     * @param  string  $table
     * @param  mixed[]  $parts
     * @return string
     */
    public function compileUpdate(string $table, array $parts): string;

    /**
     * Compile a DELETE query.
     *
     * @param  string  $table
     * @param  mixed[]  $parts
     * @return string
     */
    public function compileDelete(string $table, array $parts): string;
}
