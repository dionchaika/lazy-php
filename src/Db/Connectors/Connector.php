<?php

namespace Lazy\Db\Connectors;

use PDO;
use Lazy\Db\Connection as BaseConnection;

/**
 * The base database connector class.
 */
class Connector
{
    /**
     * The array of default PDO options.
     *
     * @var array
     */
    protected $defaultPdoOptions = [

        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION

    ];

    /**
     * Get a new database PDO connection.
     *
     * @param  array  $config
     * @return \PDO
     */
    protected function getPdo(array $config): PDO
    {
        return new PDO($this->getDsn($config), $config['user'], $config['password'], $this->defaultPdoOptions);
    }

    /**
     * Get the database connection class.
     *
     * @return string
     */
    public function getConnectionClass()
    {
        return BaseConnection::class;
    }
}
