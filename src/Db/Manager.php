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
     * @return void
     */
    public function setAsGlobal()
    {
        static::$instance = $this;
    }

    /**
     * Add a new database connector.
     *
     * @param  string  $driver
     * @param  string  $class
     *
     * @return void
     */
    public function addConnector($driver, $class)
    {
        $this->connectors[$driver] = $class;
    }
}
