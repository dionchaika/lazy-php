<?php

namespace Lazy\Db\Connectors;

use PDO;
use Exception;
use Lazy\Db\ConnectionInterface;
use Lazy\Db\Connection as BaseConnection;
use Lazy\Db\Connectors\Connector as BaseConnector;

/**
 * The MySQL database connector class.
 */
class MySQLConnector extends BaseConnector implements ConnectorInterface
{
    /**
     * The default config.
     */
    const DEFAULT_CONFIG = [

        'name'        => 'default',
        'driver'      => 'mysql',
        'user'        => null,
        'password'    => null,
        'host'        => 'localhost',
        'port'        => 3306,
        'unix_socket' => null,
        'database'    => null,
        'charset'     => 'utf8mb4',
        'collation'   => 'utf8mb4_general_ci'

    ];

    /**
     * {@inheritDoc}
     */
    public function createConnection(array $config = []): ConnectionInterface
    {
        $config = array_merge(static::DEFAULT_CONFIG, $config);

        if (in_array($config['driver'], PDO::getAvailableDrivers())) {
            return new BaseConnection($this->getPdo($config), $config);
        }

        throw new Exception("Unsupporeded database driver: {$config['driver']}!");
    }

    /**
     * Get a DSN for database PDO connection.
     *
     * @param  array  $config
     * @return string
     */
    protected function getDsn(array $config)
    {
        return empty($config['unix_socket'])
            ? $this->getHostDsn($config)
            : $this->getUnixSocketDsn($config);
    }

    /**
     * Get a host DSN for database PDO connection.
     *
     * @param  array  $config
     * @return string
     */
    protected function getHostDsn(array $config)
    {
        $dsn = 'mysql:';

        if (! empty($config['host'])) {
            $dsn .= 'host='.$config['host'];
        }

        if (! empty($config['port'])) {
            $dsn .= ';port='.$config['port'];
        }

        if (! empty($config['database'])) {
            $dsn .= ';dbname='.$config['database'];
        }

        if (! empty($config['charset'])) {
            $dsn .= ';charset='.$config['charset'];
        }

        return $dsn;
    }

    /**
     * Get a UNIX socket DSN for database PDO connection.
     *
     * @param  array  $config
     * @return string
     */
    protected function getUnixSocketDsn(array $config)
    {
        $dsn = 'mysql:unix_socket='.$config['unix_socket'];

        if (! empty($config['database'])) {
            $dsn .= ';dbname='.$config['database'];
        }

        if (! empty($config['charset'])) {
            $dsn .= ';charset='.$config['charset'];
        }

        return $dsn;
    }
}
