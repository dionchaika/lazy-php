<?php

namespace Lazy\Db;

class Query
{
    /**
     * insert...
     *
     * Note: Invoke an "INSERT" query builder.
     *
     * @param  string|null  $sql  The raw SQL for "INSERT" query.
     * @param  array  $bindings  The array of "INSERT" query value bindings.
     * @return \Lazy\Db\InsertQuery
     */
    public function insert($sql = null, $bindings = [])
    {
        return new InsertQuery($sql, $bindings);
    }

    /**
     * update...
     *
     * Note: Invoke an "UPDATE" query builder.
     *
     * @param  string|null  $sql  The raw SQL for "UPDATE" query.
     * @param  array  $bindings  The array of "UPDATE" query value bindings.
     * @return \Lazy\Db\UpdateQuery
     */
    public function update($sql = null, $bindings = [])
    {
        return new UpdateQuery($sql, $bindings);
    }
}
