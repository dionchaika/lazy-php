<?php

namespace Lazy\Db\Query;

/**
 * @property \Lazy\Db\Query\CompilerInterface $compiler
 * @property mixed[] $parts
 */
trait WhereTrait
{
    /**
     * Where...
     *
     * @param  string  $col
     * @param  mixed  $op
     * @param  mixed|null  $val
     * @return self
     */
    public function where(string $col, $op, $val = null): self
    {
        [$op, $val] = [

            (null === $val) ? '=' : $op,
            (null === $val) ? $op : $val

        ];

        $this->parts['where'][] = $col.' '.$op.' '.$this->compiler->compileVal($val);

        return $this;
    }
}
