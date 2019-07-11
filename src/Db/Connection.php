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
     * Clear the array of database connection statement log.
     *
     * @return void
     */
    public function clearLog()
    {
        $this->statementLog = [];
    }

    /**
     * Log statement.
     *
     * @param  string  $sql
     * @param  mixed|array  $bindings
     * @return void
     */
    protected function logStatement($sql, $bindings = [])
    {
        $time = microtime(true);
        $bindings = (array) $bindings;

        $this->statementLog[] = compact('time', 'sql', 'bindings');
    }
}
