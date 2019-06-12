<?php

namespace Lazy\Db\Query;

/**
 * @property \Lazy\Db\Query\CompilerInterface $compiler
 * @property mixed[] $parts
 */
trait WhereTrait
{
    /**
     * WHERE...
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

        if (null === $val) {
            return $this->whereIsNull($col);
        }

        $this->parts['where'][] = $col.' '.$op.' '.$this->compiler->compileVal($val);

        return $this;
    }

    /**
     * WHERE IS NULL...
     *
     * @param  string  $col
     * @return self
     */
    public function whereIsNull(string $col): self
    {
        $this->parts['where'][] = $col.' IS '.$this->compiler->compileVal(null);
        return $this;
    }

    /**
     * WHERE IS NOT NULL...
     *
     * @param  string  $col
     * @return self
     */
    public function whereIsNotNull(string $col): self
    {
        $this->parts['where'][] = $col.' IS NOT '.$this->compiler->compileVal(null);
        return $this;
    }
}
