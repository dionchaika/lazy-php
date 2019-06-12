<?php

namespace Lazy\Db\Query;

/**
 * @property string $db
 * @property string $table
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
     * @param  string  $delim
     * @return self
     */
    public function where(string $col, $op, $val = null, string $delim = 'AND'): self
    {
        [$op, $val] = [

            (null === $val) ? '=' : $op,
            (null === $val) ? $op : $val

        ];

        if ((null === $val || true === $val || false === $val) && '=' === $op) {
            return $this->whereIs($col, $val);
        }

        $col = $this->compiler->compileCol($col, $this->db, $this->table);
        $this->parts['where'][] = $this->chainDelim($col.' '.$op.' '.$this->compiler->compileVal($val), $delim);

        return $this;
    }

    /**
     * WHERE IS...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @param  string  $delim
     * @return self
     */
    public function whereIs(string $col, $val, string $delim = 'AND'): self
    {
        $col = $this->compiler->compileCol($col, $this->db, $this->table);
        $this->parts['where'][] = $this->chainDelim($col.' IS '.$this->compiler->compileVal($val), $delim);

        return $this;
    }

    /**
     * WHERE IS NOT...
     *
     * @param  string  $col
     * @param  mixed  $val
     * @param  string  $delim
     * @return self
     */
    public function whereIsNot(string $col, $val, string $delim = 'AND'): self
    {
        $col = $this->compiler->compileCol($col, $this->db, $this->table);
        $this->parts['where'][] = $this->chainDelim($col.' IS NOT '.$this->compiler->compileVal($val), $delim);

        return $this;
    }

    /**
     * WHERE IN...
     *
     * @param  string  $col
     * @param  mixed[]  $vals
     * @param  string  $delim
     * @return self
     */
    public function whereIn(string $col, array $vals, string $delim = 'AND'): self
    {
        $callback = function ($val) {
            return $this->compiler->compileVal($val);
        };

        $col = $this->compiler->compileCol($col, $this->db, $this->table);
        $this->parts['where'][] = $this->chainDelim($col.' IN ('.implode(', ', array_map($callback, $vals)).')', $delim);

        return $this;
    }

    /**
     * WHERE BETWEEN...
     *
     * @param  string  $col
     * @param  mixed  $min
     * @param  mixed  $max
     * @param  string  $delim
     * @return self
     */
    public function whereBetween(string $col, $min, $max, string $delim = 'AND'): self
    {
        $min = $this->compiler->compileVal($min);
        $max = $this->compiler->compileVal($max);

        $col = $this->compiler->compileCol($col, $this->db, $this->table);
        $this->parts['where'][] = $this->chainDelim($col.' BETWEEN '.$min.' AND '.$max, $delim);

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
        return empty($this->parts['where']) ? $cond : $delim.' '.$cond;
    }
}
