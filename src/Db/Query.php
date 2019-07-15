<?php

namespace Lazy\Db;

class Query
{
    /**
     * insert...
     *
     * Note: Invoke an "INSERT" query builder.
     *
     * Example:
     *      <code>
     *          $query
     *              ->insert('(id, name) values (?, ?), (?, ?)', [1, 'John', 2, 'Alex'])
     *              ->into('users', 'u')
     *              ->execute();
     *
     *          //
     *          // or...
     *          //
     *          $query
     *              ->insert()
     *              ->into('users', 'u')
     *              ->record(['id' => 1, 'name' => 'John'])
     *              ->record(['id' => 2, 'name' => 'Alex'])
     *              ->execute();
     *      </code>
     *
     * @param  string|null  $sql  The raw SQL.
     * @param  array  $bindings  The array of value bindings.
     * @return \Lazy\Db\InsertQuery
     */
    public function insert($sql = null, $bindings = [])
    {
        return new InsertQuery($sql, $bindings);
    }
}
