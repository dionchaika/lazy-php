<?php

namespace Lazy\Db\Connectors;

use PDO;
use PDOException;
use Lazy\Db\ConnectionInterface;
use Lazy\Db\Connection as BaseConnection;

/**
 * The MySQL database connector class.
 */
class MySQLConnector implements ConnectorInterface
{
    /**
     * The default config.
     */
    const DEFAULT_CONFIG = [

        'driver'      => 'mysql',
        'user'        => 'root',
        'password'    => null,
        'host'        => '127.0.0.1',
        'port'        => 3306,
        'unix_socket' => null,
        'database'    => null,
        'charset'     => 'utf8mb4',
        'collation'   => 'utf8mb4_general_ci'

    ];

    /**
     * {@inheritDoc}
     */
    public function connect(array $config = []): ConnectionInterface
    {
        return new BaseConnection($this->getPdo($config), $config);
    }
}
