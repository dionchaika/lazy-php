<?php

namespace Lazy\Db;

use Lazy\Db\Connectors\MySQLConnector;

/**
 * The database manager class.
 */
class Manager
{
    /**
     * The default config.
     */
    const DEFAULT_CONFIG = [

        'default' => [

            'driver'      => 'mysql',
            'user'        => null,
            'password'    => null,
            'host'        => 'localhost',
            'port'        => 3306,
            'unix_socket' => null,
            'database'    => null,
            'charset'     => 'utf8mb4',
            'collation'   => 'utf8mb4_general_ci'

        ]

    ];

    /**
     * The globally available
     * database manager instance.
     *
     * @var \Lazy\Db\Manager
     */
    protected static $instance;

    /**
     * The array of database connectors.
     *
     * @var array
     */
    protected $connectors = [

        'mysql' => MySQLConnector::class

    ];

    /**
     * The array of database connections.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * Set the database manager globally available.
     *
     * @return $this
     */
    public function setAsGlobal()
    {
        static::$instance = $this;

        return $this;
    }

    /**
     * Add a new database connector.
     *
     * @param  string  $driver
     * @param  string  $class
     * @return $this
     */
    public function addConnector($driver, $class)
    {
        $this->connectors[$driver] = $class;

        return $this;
    }

    /**
     * Add a new database connection.
     *
     * @param  string  $name
     * @param  \Lazy\Db\ConnectionInterface  $connection
     * @return $this
     */
    public function addConnection($name, ConnectionInterface $connection)
    {
        $this->connections[$name] = $connection;

        return $this;
    }
}
