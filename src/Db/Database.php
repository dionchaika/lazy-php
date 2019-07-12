<?php

namespace Lazy\Db;

use Lazy\Db\Connectors\MySQLConnector;

/**
 * The database manager class.
 */
class Manager
{
    /**
     * The global instance.
     *
     * @var \Lazy\Db\Database
     */
    protected static $instance;

    /**
     * The array of database manager config.
     *
     * @var array
     */
    protected $config = [];

    /**
     * The array of database manager connectors.
     *
     * @var array
     */
    protected $connectors = [

        'mysql' => MySQLConnector::class

    ];

    /**
     * The array of database manager connections.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * The database manager constructor.
     *
     * @param  array  $config  The array of database manager config.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }
}
