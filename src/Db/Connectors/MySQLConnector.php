<?php

namespace Lazy\Db\Connectors;

use PDO;
use Lazy\Db\ConnectionInterface;
use Lazy\Db\Connection as BaseConnection;

/**
 * The MySQL database connector class.
 */
class MySQLConnector extends BaseConnector implements ConnectorInterface
{
    /**
     * The default config options.
     */
    const DEFAULT_CONFIG_OPTIONS = [

        'driver'      => 'mysql',
        'host'        => '127.0.0.1',
        'port'        => 3306,
        'unix_socket' => null,
        'user'        => 'root',
        'password'    => null,
        'database'    => '',
        'charset'     => 'utf8mb4',
        'collation'   => 'urf8mb4_general_ci'

    ];

    /**
     * The default PDO options.
     */
    const DEFAULT_PDO_OPTIONS = [

        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ

    ];

    /**
     * The array of connector config.
     *
     * @var array
     */
    protected $config = [];

    /**
     * The connector constructor.
     *
     * @param  array  $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge(static::DEFAULT_CONFIG_OPTIONS, $config);
    }

    /**
     * Connect to the database.
     *
     * @return \Lazy\Db\ConnectionInterface
     */
    public function connect(): ConnectionInterface
    {
        return new BaseConnection($this->getPdo());
    }

    /**
     * Get a new PDO connection.
     *
     * @return \PDO
     */
    protected function getPdo(): PDO
    {
        $pdo = new PDO(
            $this->getDsn(),
            $this->config['user'],
            $this->config['password'],
            static::DEFAULT_PDO_OPTIONS
        );

        $this->setCollation($pdo);

        return $pdo;
    }

    /**
     * Set the connection collation.
     *
     * @param  \PDO  $pdo
     * @return void
     */
    protected function setCollation(PDO $pdo)
    {
        $pdo->prepare(
            "set names {$this->config['charset']} collate {$this->config['collation']}"
        )->execute();
    }

    /**
     * Get a DSN for PDO connection.
     *
     * @return string
     */
    protected function getDsn()
    {
        return empty($this->config['unix_socket'])
            ? $this->getHostDsn()
            : $this->getUnixSocketDsn();
    }

    /**
     * Get a host DSN for PDO connection.
     *
     * @return string
     */
    protected function getHostDsn()
    {
        $dsn = 'mysql:';

        if (! empty($this->config['host'])) {
            $dsn .= 'host='.$this->config['host'];
        }

        if (! empty($this->config['port'])) {
            $dsn .= ';port='.$this->config['port'];
        }

        if (! empty($this->config['database'])) {
            $dsn .= ';dbname='.$this->config['database'];
        }

        if (! empty($this->config['charset'])) {
            $dsn .= ';charset='.$this->config['charset'];
        }

        return $dsn;
    }

    /**
     * Get a UNIX socket DSN for PDO connection.
     *
     * @return string
     */
    protected function getUnixSocketDsn()
    {
        $dsn = 'mysql:unix_socket='.$this->config['unix_socket'];

        if (! empty($this->config['database'])) {
            $dsn .= ';dbname='.$this->config['database'];
        }

        if (! empty($this->config['charset'])) {
            $dsn .= ';charset='.$this->config['charset'];
        }

        return $dsn;
    }
}
