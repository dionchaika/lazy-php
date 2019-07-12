<?php

namespace Lazy\Db;

use PDO;
use Closure;
use Exception;
use PDOException;
use PDOStatement;

/**
 * The base database connection class.
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
     * The array of database connection config.
     *
     * @var array
     */
    protected $config = [];

    /**
     * The database connection constructor.
     *
     * @param  \PDO  $pdo  The database PDO connection.
     * @param  array  $config  The array of database connection config.
     */
    public function __construct(PDO $pdo, array $config = [])
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        $this->pdo = null;
    }

    /**
     * {@inheritDoc}
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig($name = null)
    {
        if (! $name) {
            return $this->config;
        }

        return isset($this->config[$name]) ? $this->config[$name] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function select($sql, $bindings = [])
    {
        return $this->statement($sql, $bindings)->fetchAll();
    }

    /**
     * {@inheritDoc}
     */
    public function insert($sql, $bindings = [])
    {
        return $this->affectingStatement($sql, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function update($sql, $bindings = [])
    {
        return $this->affectingStatement($sql, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($sql, $bindings = [])
    {
        return $this->affectingStatement($sql, $bindings);
    }

    /**
     * {@inheritDoc}
     */
    public function execute($sql, $bindings = [])
    {
        return $this->executeCallback($sql, $bindings, function ($sql, $bindings) {
            $statement = $this->pdo->prepare($sql);

            $this->bindValues($statement, $bindings);

            return $statement->execute();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function bindValues(PDOStatement $statement, $bindings = [])
    {
        $bindings = (array) $bindings;

        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_int($key) ? $key + 1 : $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * Excecute a statement.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return \PDOStatement
     *
     * @throws \Exception
     */
    protected function statement($sql, $bindings = []): PDOStatement
    {
        return $this->executeCallback($sql, $bindings, function ($sql, $bindings) {
            $statement = $this->pdo->prepare($sql);

            $this->bindValues($statement, $bindings);

            $statement->execute();

            return $statement;
        });
    }

    /**
     * Excecute a statement and return the number of affected rows.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return int
     *
     * @throws \Exception
     */
    protected function affectingStatement($sql, $bindings = [])
    {
        return $this->executeCallback($sql, $bindings, function ($sql, $bindings) {
            $statement = $this->pdo->prepare($sql);

            $this->bindValues($statement, $bindings);

            $statement->execute();

            return $statement->rowCount();
        });
    }

    /**
     * Execute a statement within the callback.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Exception
     */
    protected function executeCallback($sql, $bindings = [], Closure $callback)
    {
        $startTime = microtime(true);

        try {
            $result = call_user_func(
                $callback, $sql, $bindings
            );
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }

        $executionTime = microtime(true) - $startTime;

        return is_numeric($result) ? (int) $result : $result;
    }
}
