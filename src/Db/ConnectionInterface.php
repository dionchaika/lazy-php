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
     * Execute an insert statement.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return bool
     */
    public function insert($sql, $bindings = []);

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
     * Bind values to parameters in the statement.
     *
     * @param  \PDOStatement  $statement
     * @param  mixed|array  $bindings
     * @return void
     */
    public function bindValues(PDOStatement $statement, $bindings = []);
}
