<?php

namespace Lazy\Db\Query;

use Lazy\Db\Query\Compilers\Compiler;
use Lazy\Db\Connection\Connections\PDOConnection;

class Builder
{
    use WhereTrait;

    /**
     * The builder table.
     *
     * @var string
     */
    protected $table;

    /**
     * The builder compiler.
     *
     * @var \Lazy\Db\Query\CompilerInterface
     */
    protected $compiler;

    /**
     * The builder connection.
     *
     * @var \Lazy\Db\Connection\ConnectionInterface
     */
    protected $connection;

    /**
     * The query parts.
     *
     * @var mixed[]
     */
    protected $parts = [

        'select'   => [],
        'distinct' => false,
        'where'    => [],
        'orderBy'  => [],
        'limit'    => null

    ];

    /**
     * The builder constructor.
     *
     * @param  string  $table
     * @param  \Lazy\Db\Query\CompilerInterface|null  $compiler
     * @param  \Lazy\Db\Connection\ConnectionInterface|null  $connection
     */
    public function __construct(string $table, ?CompilerInterface $compiler = null, ?ConnectionInterface $connection = null)
    {
        $this->table = $table;
        $this->compiler = $compiler ?? new Compiler;
        $this->connection = $connection ?? new PDOConnection;
    }

    /**
     * DISTINCT...
     *
     * @return self
     */
    public function distinct(): self
    {
        $this->parts['distinct'] = true;
        return $this;
    }
}
