<?php

namespace Lazy\Db\Query;

trait WhereTrait
{
    /**
     * The array of query where clauses.
     *
     * @var mixed[]
     */
    protected $wheres = [];

    /**
     * where...
     *
     * @param  string  $col
     * @param  mixed|null  $op
     * @param  mixed|null  $val
     * @param  string  $delim
     * @param  bool  $not
     * @return self
     */
    public function where(string $col, $op = null, $val = null, string $delim = 'and', bool $not = false): self
    {
        if (1 === func_num_args()) {
            return $this->whereIsNot($col, null);
        }

        [$op, $val] = (null === $val) ? ['=', $op] : [$op, $val];

        if ((null === $val || true === $val || false === $val) && '=' === $op) {
            return $this->whereIs($col, $val);
        }

        $this->wheres[] = [

            'not'   => $not,
            'delim' => $delim,
            'col'   => $col,
            'op'    => $op,
            'val'   => $val

        ];

        return $this;
    }

    /**
     * where not...
     *
     * @param  string  $col
     * @param  mixed|null  $op
     * @param  mixed|null  $val
     * @param  string  $delim
     * @return self
     */
    public function whereNot(string $col, $op = null, $val = null, string $delim = 'and'): self
    {
        return $this->where($col, $op, $val, $delim, true);
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
        return $this->where($col, 'is', $val, $delim);
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
        return $this->where($col, 'is not', $val, $delim);
    }
}
