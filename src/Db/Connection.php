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
        return $this->execute($sql, $bindings)->fetchAll();
    }

    /**
     * {@inheritDoc}
     */
    public function insert($sql, $bindings = [])
    {
        return $this->execute($sql, $bindings)->rowCount();
    }

    /**
     * {@inheritDoc}
     */
    public function update($sql, $bindings = [])
    {
        return $this->execute($sql, $bindings)->rowCount();
    }

    /**
     * {@inheritDoc}
     */
    public function delete($sql, $bindings = [])
    {
        return $this->execute($sql, $bindings)->rowCount();
    }

    /**
     * {@inheritDoc}
     */
    public function selectGetFirst($sql, $bindings = [])
    {
        $rows = $this->select($sql, $bindings);

        return array_shift($rows);
    }

    /**
     * {@inheritDoc}
     */
    public function selectGetLast($sql, $bindings = [])
    {
        $rows = $this->select($sql, $bindings);

        return array_pop($rows);
    }

    /**
     * {@inheritDoc}
     */
    public function selectGetRand($sql, $bindings = [])
    {
        $rows = $this->select($sql, $bindings);

        return $rows[rand(0, count($rows) - 1)];
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
}
