<?php

namespace Lazy\Db\Connectors;

use Lazy\Db\ConnectionInterface;

interface ConnectorInterface
{
    /**
     * Connect to the database.
     *
     * @param  array  $config
     * @return \Lazy\Db\ConnectionInterface
     */
    public function connect(array $config = []): ConnectionInterface;
}
