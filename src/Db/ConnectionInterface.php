<?php

namespace Lazy\Db;

use PDO;
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
     * Bind values to parameters in the statement.
     *
     * @param  \PDOStatement  $statement
     * @param  mixed|array  $bindings
     * @return void
     */
    public function bindValues(PDOStatement $statement, $bindings = []);
}
