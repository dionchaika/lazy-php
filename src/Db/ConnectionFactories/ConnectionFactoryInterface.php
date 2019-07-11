<?php

namespace Lazy\Db\ConnectionFactories;

use Lazy\Db\ConnectionInterface;

interface ConnectionFactoryInterface
{
    /**
     * Create a new database connection.
     *
     * @param  array  $config
     * @return \Lazy\Db\ConnectionInterface
     */
    public function createConnection(array $config = []): ConnectionInterface;
}
