<?php

namespace Lazy\Db;

/**
 * The database query class.
 */
class Query
{
    /**
     * The query types.
     */
    const SELECT = 0;
    const INSERT = 1;
    const UPDATE = 2;
    const DELETE = 3;

    /**
     * The query database connection.
     *
     * @var \Lazy\Db\ConnectionInterface|null
     */
    protected $connection;

    /**
     * The database query constructor.
     *
     * @param  \Lazy\Db\ConnectionInterface|null  $connection  The query database connection.
     */
    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->connection = $connection;
    }
}
