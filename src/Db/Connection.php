<?php

namespace Lazy\Db;

use PDO;
use PDOException;
use PDOStatement;

/**
 * The database connection base class.
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
     * The array of database connection log.
     *
     * @var array
     */
    protected $log = [];

    /**
     * The array of database connection config options.
     *
     * @var array
     */
    protected $config = [];

    /**
     * The database connection constructor.
     *
     * @param  \PDO  $pdo  The database PDO connection.
     * @param  array  $config  The array of database connection config options.
     */
    public function __construct(PDO $pdo, array $config = [])
    {
        $this->pdo = $pdo;
        $this->config = $config;

        if (PDO::ERRMODE_EXCEPTION !== $this->pdo->getAttribute(PDO::ATTR_ERRMODE)) {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
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
    public function getLog()
    {
        return $this->log;
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
    public function selectFirst($sql, $bindings = [])
    {
        $rows = $this->select($sql, $bindings);

        return array_shift($rows);
    }

    /**
     * {@inheritDoc}
     */
    public function selectLast($sql, $bindings = [])
    {
        $rows = $this->select($sql, $bindings);

        return array_pop($rows);
    }

    /**
     * {@inheritDoc}
     */
    public function selectRand($sql, $bindings = [])
    {
        $rows = $this->select($sql, $bindings);

        return $rows[rand(0, count($rows) - 1)];
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
    public function insertGetId($sql, $bindings = [])
    {
        $this->insert($sql, $bindings);

        $lastInsertId = $this->pdo->lastInsertId();

        return is_numeric($lastInsertId) ? (int) $lastInsertId : $lastInsertId;
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
     * Log.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @param  string  $message
     * @return void
     */
    protected function log($sql, $bindings = [], $message = '')
    {
        $bindings = array_values((array) $bindings);

        $this->log[] = compact('sql', 'bindings', 'message');
    }

    /**
     * Execute a statement.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return \PDOStatement
     */
    protected function statement($sql, $bindings = []): PDOStatement
    {
        try {
            $statement = $this->pdo->prepare($sql);

            $this->bindValues($statement, $bindings);

            $statement->execute();

            $this->log($sql, $bindings);

            return $statement;
        } catch (PDOException $e) {
            $this->log($sql, $bindings, $e->getMessage());
        }
    }

    /**
     * Execute a statement and return the number of affected rows.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return int
     */
    protected function affectingStatement($sql, $bindings = [])
    {
        return $this->statement($sql, $bindings)->rowCount();
    }
}
