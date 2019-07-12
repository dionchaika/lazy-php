<?php

namespace Lazy\Db;

use PDO;
use Closure;
use PDOStatement;

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
     * Get the database connection config.
     *
     * @param  string|null  $name
     * @return array|mixed|null
     */
    public function getConfig($name = null);

    /**
     * Get the database PDO connection fetch mode.
     *
     * @return int
     */
    public function getFetchMode();

    /**
     * Set the database PDO connection fetch mode.
     *
     * @param  int  $fetchMode
     * @return void
     */
    public function setFetchMode($fetchMode);

    /**
     * Get the array of database connection statement log.
     *
     * @return array
     */
    public function getStatementLog();

    /**
     * Clear the array of database connection statement log.
     *
     * @return void
     */
    public function clearStatementLog();

    /**
     * Commit the transaction.
     *
     * @return void
     */
    public function commit();

    /**
     * Roll back the transaction.
     *
     * @return void
     */
    public function rollBack();

    /**
     * Begin a new transaction.
     *
     * @return void
     */
    public function beginTransaction();

    /**
     * Execute a callback within the transaction.
     *
     * @param  \Closure  $callback
     * @return mixed
     */
    public function transaction(Closure $callback);

    /**
     * Execute a select statement.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return array
     */
    public function select($sql, $bindings = []);

    /**
     * Execute a select statement and return the first row.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return mixed
     */
    public function selectFirst($sql, $bindings = []);

    /**
     * Execute a select statement and return the last row.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return mixed
     */
    public function selectLast($sql, $bindings = []);

    /**
     * Execute a select statement and return the random row.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return mixed
     */
    public function selectRand($sql, $bindings = []);

    /**
     * Execute an insert statement.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return bool
     */
    public function insert($sql, $bindings = []);

    /**
     * Execute an insert statement
     * and return the last inserted row ID.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return int|string
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

    /**
     * Execute a statement and return the boolean result.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return bool
     */
    public function statement($sql, $bindings = []);

    /**
     * Execute a statement and return the number of affected rows.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return int
     */
    public function affectingStatement($sql, $bindings = []);

    /**
     * Bind values to parameters in the statement.
     *
     * @param  \PDOStatement  $statement
     * @param  mixed|array  $bindings
     * @return void
     */
    public function bindValues(PDOStatement $statement, $bindings = []);
}
