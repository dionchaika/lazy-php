<?php

namespace Lazy\Db;

use PDO;

interface ConnectionInterface
{
    /**
     * Close the database connection.
     *
     * @return void
     */
    public function close();

    /**
     * Get the database PDO connection.
     *
     * @return \PDO
     */
    public function getPdo(): PDO;

    /**
     * Execute a select statement.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return array
     */
    public function select($sql, $bindings = []);

    /**
     * Execute a select statement and return the first selected row.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return mixed
     */
    public function selectFirst($sql, $bindings = []);

    /**
     * Execute a select statement and return the last selected row.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return mixed
     */
    public function selectLast($sql, $bindings = []);

    /**
     * Execute a select statement and return the random selected row.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return mixed
     */
    public function selectRandom($sql, $bindings = []);

    /**
     * Execute an insert statement.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return int
     */
    public function insert($sql, $bindings = []);

    /**
     * Execute an insert statement and return the last inserted row ID.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return int
     */
    public function insertGetId($sql, $bindings = []);

    /**
     * Execute an update statement.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return int
     */
    public function update($sql, $bindings = []);

    /**
     * Execute a delete statement.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return int
     */
    public function delete($sql, $bindings = []);
}
