<?php

namespace Lazy\Db;

use PDO;
use Closure;
use Throwable;
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
     * The array of database connection statement log.
     *
     * @var array
     */
    protected $statementLog = [];

    /**
     * The current database connection transaction level.
     *
     * @var int
     */
    protected $transactionLevel = 0;

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
    public function getStatementLog()
    {
        return $this->statementLog;
    }

    /**
     * {@inheritDoc}
     */
    public function clearStatementLog()
    {
        $this->statementLog = [];
    }

    /**
     * {@inheritDoc}
     */
    public function commit()
    {
        if (1 === $this->transactionLevel) {
            $this->pdo->commit();
        }

        $this->transactionLevel = max(0, $this->transactionLevel - 1);
    }

    /**
     * {@inheritDoc}
     */
    public function rollBack()
    {
        if (1 === $this->transactionLevel) {
            $this->pdo->rollBack();
        } else if ($this->isSupportsSavepoints()) {
            $savepoint = $this->transactionLevel - 1;

            if (0 >= $savepoint) {
                return;
            }

            $this->pdo->exec(
                $this->getSqlForSavepointRollBack('savepoint'.$savepoint)
            );
        }

        $this->transactionLevel = max(0, $this->transactionLevel - 1);
    }

    /**
     * {@inheritDoc}
     */
    public function beginTransaction()
    {
        if (0 === $this->transactionLevel) {
            $this->pdo->beginTransaction();
        } else if ($this->isSupportsSavepoints()) {
            $this->pdo->exec(
                $this->getSqlForSavepoint('savepoint'.$this->transactionLevel)
            );
        }

        $this->transactionLevel++;
    }

    /**
     * {@inheritDoc}
     */
    public function transaction(Closure $callback)
    {
        $this->beginTransaction();

        try {

            $result = $callback($this);

            $this->commit();

            return $result;

        } catch (Throwable $e) { $this->rollBack(); throw $e; }
    }

    /**
     * {@inheritDoc}
     */
    public function select($sql, $bindings = [])
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function insert($sql, $bindings = [])
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function update($sql, $bindings = [])
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function delete($sql, $bindings = [])
    {
        //
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
     * Run a statement.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @param  \Closure  $callback
     * @return void
     */
    protected function run($sql, $bindings = [], Closure $callback)
    {
        $startTime = microtime(true);

        $result = $callback($sql, $bindings);

        $executionTime = microtime(true) - $startTime;

        $this->logStatement($sql, $bindings, $executionTime);

        return $result;
    }

    /**
     * Log a statement.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @param  float  $executionTime
     * @return void
     */
    public function logStatement($sql, $bindings = [], $executionTime)
    {
        $bindings = (array) $bindings;

        $this->statementLog[] = compact('sql', 'bindings', 'executionTime');
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
