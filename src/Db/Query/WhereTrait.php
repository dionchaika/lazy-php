<?php

namespace Lazy\Db\Query;

/**
 * @property mixed[] $conditions
 * @property \Lazy\Db\Query\CompilerInterface $compiler
 */
trait WhereTrait
{
    /**
     * where...
     *
     * @param  string  $col
     * @param  mixed  $op
     * @param  mixed|null  $val
     * @param  string  $delim
     * @return self
     */
    public function where(string $col, $op, $val = null, string $delim = 'and'): self
    {
        [$op, $val] = [

            (null === $val) ? '=' : $op,
            (null === $val) ? $op : $val

        ];

        if ((null === $val || true === $val || false === $val) && '=' === $op) {
            return $this->whereIs($col, $val);
        }

        $this->conditions[] = $this->chainDelim(
            $col.' '.$op.' '.$this->compiler->compileVal($val), $delim
        );

        return $this;
    }

    /**
     * where is...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @param  string  $delim
     * @return self
     */
    public function whereIs(string $col, $val, string $delim = 'and'): self
    {
        $this->conditions[] = $this->chainDelim(
            $col.' is '.$this->compiler->compileVal($val), $delim
        );

        return $this;
    }

    /**
     * where is not...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @param  string  $delim
     * @return self
     */
    public function whereIsNot(string $col, $val, string $delim = 'and'): self
    {
        $this->conditions[] = $this->chainDelim(
            $col.' is not '.$this->compiler->compileVal($val), $delim
        );

        return $this;
    }

    /**
     * where in...
     *
     * @param  string  $col
     * @param  mixed[]  $vals
     * @param  string  $delim
     * @return self
     */
    public function whereIn(string $col, array $vals, string $delim = 'and'): self
    {
        $in = implode(', ', array_map(function ($val) {
            $this->compiler->compileVal($val);
        }, $vals));

        $this->conditions[] = $this->chainDelim($col.' in ('.$in.')', $delim);

        return $this;
    }

    /**
     * where like...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @param  int|null  $criteria
     * @param  string  $delim
     * @return self
     */
    public function whereLike(string $col, $val, ?int $criteria = null, string $delim = 'and'): self
    {
        $val = $this->compiler->compileWhereLike($val, $criteria);
        $this->conditions[] = $this->chainDelim($col.' like '.$val, $delim);

        return $this;
    }

    /**
     * where between...
     *
     * @param  string  $col
     * @param  mixed  $min
     * @param  mixed  $max
     * @param  string  $delim
     * @return self
     */
    public function whereBetween(string $col, $min, $max, string $delim = 'and'): self
    {
        $min = $this->compiler->compileVal($min);
        $max = $this->compiler->compileVal($max);

        $this->conditions[] = $this->chainDelim($col.' between '.$min.' and '.$max, $delim);

        return $this;
    }

    /**
     * Chain a condition with a delimiter.
     *
     * @param  string  $cond
     * @param  string  $delim
     * @return string
     */
    protected function chainDelim(string $cond, string $delim): string
    {
        return empty($this->conditions) ? $cond : $delim.' '.$cond;
    }
}
