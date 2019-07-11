<?php

namespace Lazy\Db;

use PDO;

/**
 * The database connection class.
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
     * The database connection constructor.
     *
     * @param  \PDO  $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get the database PDO connection.
     *
     * @return \PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
