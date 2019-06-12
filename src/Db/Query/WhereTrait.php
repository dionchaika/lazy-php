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

        if ((null === $val || true === $val || false === $val) && '=' === $op) {
            return $this->whereIs($col, $val);
        }

        $this->parts['where'][] = $col.' '.$op.' '.$this->compiler->compileVal($val);

        return $this;
    }

    /**
     * WHERE IS...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @return self
     */
    public function whereIs(string $col, $val): self
    {
        $this->parts['where'][] = $col.' IS '.$this->compiler->compileVal($val);
        return $this;
    }

    /**
     * WHERE IS NOT...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @return self
     */
    public function whereIsNot(string $col, $val): self
    {
        $this->parts['where'][] = $col.' IS NOT '.$this->compiler->compileVal($val);
        return $this;
    }

    /**
     * WHERE IN...
     *
     * @param  string  $col
     * @param  mixed[]  $vals
     * @return self
     */
    public function whereIn(string $col, array $vals): self
    {
        $callback = function ($val) {
            return $this->compiler->compileVal($val);
        };

        $this->parts['where'][] = $col.' IN ('.implode(', ', array_map($callback, $vals)).')';

        return $this;
    }

    /**
     * WHERE BETWEEN...
     *
     * @param  string  $col
     * @param  mixed  $min
     * @param  mixed  $max
     * @return self
     */
    public function whereBetween(string $col, $min, $max): self
    {
        $min = $this->compiler->compileVal($min);
        $max = $this->compiler->compileVal($max);

        $this->parts['where'][] = $col.' BETWEEN '.$min.' AND '.$max;

        return $this;
    }
}
