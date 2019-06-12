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

        $this->parts['where'][] = $col.' '.$op.' '.$val;

        return $this;
    }
}
