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
}
