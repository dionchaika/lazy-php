<?php

namespace Lazy\Db\Connectors;

use Lazy\Db\ConnectionInterface;

interface ConnectorInterface
{
    /**
     * Create a new database connection.
     *
     * @param  array  $config
     * @return \Lazy\Db\ConnectionInterface
     *
     * @throws \Exception
     */
    public function createConnection(array $config = []): ConnectionInterface;
}
