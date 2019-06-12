<?php

namespace Lazy\Db\Query;

trait SelectTrait
{
    /**
     * The array of columns.
     *
     * @var string[]
     */
    protected $cols = [];

    /**
     * The array of aliases.
     *
     * @var string[]
     */
    protected $aliases = [];

    public function select($cols = '*'): self
    {
        $this->setQueryType()
    }
}
