<?php

namespace Lazy\Db\Connectors;

use PDO;

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
     * Create a new PDO connection.
     *
     * @param  array  $config
     * @return \PDO
     */
    protected function createPdo(array $config): PDO
    {
        return new PDO($this->getDsn($config), $config['user'], $config['password'], $this->defaultPdoOptions);
    }
}
