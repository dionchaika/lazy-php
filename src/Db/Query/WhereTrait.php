<?php

namespace Lazy\Db\Query;

/**
 * @property mixed[] $parts
 */
trait WhereTrait
{
    /**
     * Where...
     *
     * @param  string  $col
     * @param  string  $op
     * @param  mixed|null  $val
     * @param  string  $delim
     * @return self
     */
    public function where(string $col, ?string $op, $val = null, string $delim = 'AND'): self
    {
        [$op, $val] = [

            (null === $val) ? '=' : $op,
            (null === $val) ? $op : $val

        ];

        $this->parts['where'][] = $col.' '.$op.' '.$val;

        return $this;
    }
}
