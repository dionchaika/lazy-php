<?php

namespace Lazy\Db;

use PDO;

interface ConnectionInterface
{
    /**
     * Get the database PDO connection.
     *
     * @return \PDO
     */
    public function getPdo(): PDO;
}
