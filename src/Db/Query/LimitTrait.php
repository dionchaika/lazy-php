<?php

namespace Lazy\Db\Query;

trait LimitTrait
{
    /**
     * The query limit.
     *
     * @var int
     */
    protected $limit;

    /**
     * The query offset.
     *
     * @var int
     */
    protected $offset;

    /**
     * Set the query limit.
     *
     * @param  int  $limit
     * @return \Lazy\Db\Query\Builder
     */
    public function limit(int $limit): Builder
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set the query offset.
     *
     * @param  int  $offset
     * @return \Lazy\Db\Query\Builder
     */
    public function offset(int $offset): Builder
    {
        $this->offset = $offset;
        return $this;
    }
}
