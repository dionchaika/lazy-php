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

        'name'        => 'default',
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
     * The default PDO options.
     */
    const DEFAULT_PDO_OPTIONS = [

        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION

    ];

    /**
     * {@inheritDoc}
     */
    public function createConnection(array $config = []): ConnectionInterface
    {
        $config = array_merge(static::DEFAULT_CONFIG, $config);

        $connection = new BaseConnection($this->getPdo($config), $config);

        $connection
            ->getPdo()
            ->prepare('set names ? collate ?')
            ->execute($config['charset'], $config['collation']);

        return $connection;
    }

    /**
     * Get a new database PDO connection.
     *
     * @param  array  $config
     * @return \PDO
     */
    protected function getPdo(array $config): PDO
    {
        try {
            return new PDO(
                $this->getDsn($config),
                $config['user'],
                $config['password'],
                static::DEFAULT_PDO_OPTIONS
            );
        } catch (PDOException $e) {}
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
