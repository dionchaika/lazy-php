<?php

namespace Lazy\Db\Query;

trait GroupByTrait
{
    /**
     * The array
     * of query group by clauses.
     *
     * @var mixed[]
     */
    public $groupsBy = [];

    /**
     * group by...
     *
     * @param  mixed  $cols
     * @return self
     */
    public function groupBy($cols): self
    {
        $this->groupsBy = is_array($cols)
            ? $cols
            : func_get_args();

        return $this;
    }
}
