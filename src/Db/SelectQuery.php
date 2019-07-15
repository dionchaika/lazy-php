<?php

namespace Lazy\Db\Query;

class SelectQuery
{
    protected $table;

    protected $columns = [];

    public function __construct($columns = [])
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
    }

    public function from($table, $alias = null)
    {
        $this->table = compact('table', 'alias');
    }

    public function where($column, $operator, $value)
    {
        //
    }
}
