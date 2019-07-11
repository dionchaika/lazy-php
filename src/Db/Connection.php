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

        $this->pdo->setAttribute(
            PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ
        );
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
     * @param  array|mixed  $bindings
     * @return array
     */
    public function select($sql, $bindings = [])
    {
        return $this->statement($sql, $bindings)->fetchAll();
    }

    /**
     * Run a select statement and return the first selected row.
     *
     * @param  string  $sql
     * @param  array|mixed  $bindings
     * @return mixed
     */
    public function selectFirst($sql, $bindings = [])
    {
        $rows = $this->select($sql, $bindings);

        return array_shift($rows);
    }

    /**
     * Run an insert statement.
     *
     * @param  string  $sql
     * @param  array|mixed  $bindings
     * @return int
     */
    public function insert($sql, $bindings = [])
    {
        return $this->affectedStatement($sql, $bindings);
    }

    /**
     * Run an insert statement and return the last inserted row ID.
     *
     * @param  string  $sql
     * @param  array|mixed  $bindings
     * @return int|string
     */
    public function insertGetId($sql, $bindings = [])
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
     * @param  array|mixed  $bindings
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
     * @param  array|mixed  $bindings
     * @return \PDOStatement
     */
    public function statement($sql, $bindings = []): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);

        $this->bindParams($statement, $bindings);

        $statement->execute();

        return $statement;
    }

    /**
     * Execute statement and return the number of affected rows.
     *
     * @param  string  $sql
     * @param  array|mixed  $bindings
     * @return int
     */
    public function affectedStatement($sql, $bindings = [])
    {
        return $this->statement($sql, $bindings)->rowCount();
    }

    /**
     * Bind values to their parameters in the statement.
     *
     * @param  \PDOStatement  $statement
     * @param  array|mixed  $bindings
     * @return void
     */
    public function bindParams(PDOStatement $statement, $bindings = [])
    {
        $bindings = (array) $bindings;

        foreach ($bindings as $key => $value) {
            $statement->bindValue(is_int($key) ? $key + 1 : $key,
                                  $value,
                                  is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }
}
