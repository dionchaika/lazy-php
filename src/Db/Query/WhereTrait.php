<?php

namespace Lazy\Db\Query;

use Closure;

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
     * @param  \Closure|string  $col
     * @param  mixed|null  $op
     * @param  mixed|null  $val
     * @param  string  $delim
     * @param  bool  $not
     * @return self
     */
    public function where($col, $op = null, $val = null, string $delim = 'and', bool $not = false): self
    {
        if ($col instanceof Closure) {
            $this->wheres[] = '(';
            $col($this);
            $this->wheres[] = ')';

            return $this;
        }

        $type = 'basic';

        [$op, $val] = (null === $val) ? ['=', $op] : [$op, $val];

        $this->wheres[] = compact('type', 'col', 'op', 'val', 'delim', 'not');

        return $this;
    }
}
