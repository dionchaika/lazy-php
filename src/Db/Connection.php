<?php

namespace Lazy\Db;

use PDO;
use PDOStatement;

/**
 * The database connection class.
 */
class Connection implements ConnectionInterface
{
    /**
     * The database PDO connection.
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * The database connection constructor.
     *
     * @param  \PDO  $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * The database connection destructor.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Close the database connection.
     *
     * @return void
     */
    public function close()
    {
        $this->pdo = null;
    }

    /**
     * Get the database PDO connection.
     *
     * @return \PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Run a select statement.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return array
     */
    public function select($sql, array $bindings = [])
    {
        return $this->statement($sql, $bindings)->fetchAll();
    }

    /**
     * Run an insert statement.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return int
     */
    public function insert($sql, array $bindings = [])
    {
        return $this->affectedStatement($sql, $bindings);
    }

    /**
     * Run an insert statement and return the last inserted row ID.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return int|string
     */
    public function insertGetId($sql, array $bindings = [])
    {
        $this->insert($sql, $bindings);

        $lastInsertId = $this->pdo->lastInsertId();

        return is_numeric($lastInsertId) ? (int) $lastInsertId : $lastInsertId;
    }

    /**
     * Run an update statement.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return int
     */
    public function update($sql, $bindings = [])
    {
        return $this->affectedStatement($sql, $bindings);
    }

    /**
     * Run a delete statement.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return int
     */
    public function delete($sql, $bindings = [])
    {
        return $this->affectedStatement($sql, $bindings);
    }

    /**
     * Execute statement.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return \PDOStatement
     */
    public function statement($sql, array $bindings = []): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);

        $this->bindParams($statement, $bindings);

        $statement->execute();

        return $statement;
    }

    /**
     * Execute statement and return the number of affected raws.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return int
     */
    public function affectedStatement($sql, array $bindings = [])
    {
        return $this->statement($sql, $bindings)->rowCount();
    }

    /**
     * Bind values to their parameters in the statement.
     *
     * @param  \PDOStatement  $statement
     * @param  array  $bindings
     * @return void
     */
    public function bindParams(PDOStatement $statement, array $bindings = [])
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(is_int($key) ? $key + 1 : $key,
                                  $value,
                                  is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }
}
