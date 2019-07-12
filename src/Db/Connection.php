<?php

namespace Lazy\Db;

use PDO;
use Closure;
use Throwable;
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
     * The number of current transaction savepoint.
     *
     * @var int
     */
    protected $savepointNumber = 0;

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
    public function commit()
    {
        if (1 === $this->savepointNumber) {
            $this->pdo->commit();
        }

        $this->savepointNumber = max(0, $this->savepointNumber - 1);
    }

    /**
     * {@inheritDoc}
     */
    public function rollBack()
    {
        if (1 === $this->savepointNumber) {
            $this->pdo->rollBack();
        } else if ($this->isSupportsSavepoints()) {
            $this->pdo->exec(
                $this->getSqlForSavepointRollBack('savepoint'.$this->savepointNumber)
            );
        }

        $this->savepointNumber = max(0, $this->savepointNumber - 1);
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction()
    {
        if (0 === $this->savepointNumber) {
            $this->pdo->beginTransaction();
        } else if ($this->isSupportsSavepoints()) {
            $this->pdo->exec(
                $this->getSqlForSavepoint('savepoint'.$this->savepointNumber)
            );
        }

        $this->savepointNumber++;
    }

    /**
     * {@inheritDoc}
     */
    public function transaction(Closure $callback)
    {
        $this->beginTransaction();

        try {
            $result = call_user_func($callback, $this);
        } catch (Throwable $e) {
            $this->rollBack();

            throw $e;
        }

        $this->commit();

        return $result;
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
     * Check is the database
     * connection supports savepoints.
     *
     * @return bool
     */
    protected function isSupportsSavepoints()
    {
        return true;
    }

    /**
     * Get an SQL for database connection savepoint.
     *
     * @param  string  $name
     * @return string
     */
    protected function getSqlForSavepoint($name)
    {
        return 'SAVEPOINT '.$name;
    }

    /**
     * Get an SQL for database connection savepoint roll back.
     *
     * @param  string  $name
     * @return string
     */
    protected function getSqlForSavepointRollBack($name)
    {
        return 'ROLLBACK TO SAVEPOINT '.$name;
    }
}
