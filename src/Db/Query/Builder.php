<?php

namespace Lazy\Db\Query;

use Lazy\Db\Query\Compilers\Compiler;
use Lazy\Db\Connection\Connections\PDOConnection;

class Builder
{
    use WhereTrait;

    /**
     * The query types.
     */
    const SELECT = 0;
    const INSERT = 1;
    const UPDATE = 2;
    const DELETE = 3;

    /**
     * The current query type.
     *
     * @var int
     */
    public $queryType = self::SELECT;

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
     * SELECT...
     *
     * @param  mixed  $cols
     * @return self
     */
    public function select($cols = '*'): self
    {
        $this->setQueryType(static::SELECT);

        $cols = is_array($cols) ? $cols : func_get_args();

        $this->parts['select'] = $cols;

        return $this;
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

    /**
     * ORDER BY...
     *
     * @param  mixed  $cols
     * @param  string  $order
     * @return self
     */
    public function orderBy($cols, string $order = 'DESC'): self
    {
        $cols = is_array($cols) ? $cols : [$cols];

        $this->parts['orderBy'][] = implode(', ', $cols).' '.$order;

        return $this;
    }

    /**
     * ORDER BY ASC...
     *
     * @param  mixed  $cols
     * @return self
     */
    public function orderByAsc($cols): self
    {
        $cols = is_array($cols) ? $cols : func_get_args();
        return $this->orderBy($cols, 'ASC');
    }

    /**
     * ORDER BY DESC...
     *
     * @param  mixed  $cols
     * @return self
     */
    public function orderByDesc($cols): self
    {
        $cols = is_array($cols) ? $cols : func_get_args();
        return $this->orderBy($cols, 'DESC');
    }

    /**
     * LIMIT...
     *
     * @param  int  $count
     * @param  int|null  $offset
     * @return self
     */
    public function limit(int $count, ?int $offset = null): self
    {
        $this->parts['limit'] = (null === $offset) ? $count : $offset.', '.$count;
        return $this;
    }

    /**
     * Set the current query type.
     *
     * @param  int  $type
     * @return void
     */
    protected function setQueryType(int $type): void
    {
        $this->queryType = $type;

        $this->parts['select'] = [];
        $this->parts['distinct'] = false;
        $this->parts['where'] = [];
        $this->parts['orderBy'] = [];
        $this->parts['limit'] = null;
    }
}
