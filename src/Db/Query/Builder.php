<?php

namespace Lazy\Db\Query;

use Throwable;
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
     * The query db.
     *
     * @var string
     */
    protected $db;

    /**
     * The query table.
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
     * @param  string|null  $db
     * @param  string|null  $table
     * @param  \Lazy\Db\Query\CompilerInterface|null  $compiler
     * @param  \Lazy\Db\Connection\ConnectionInterface|null  $connection
     */
    public function __construct(
        ?string $db = null,
        ?string $table = null,
        ?CompilerInterface $compiler = null,
        ?ConnectionInterface $connection = null
    ) {
        $this->db = $db;
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

        $this->parts['select'] = array_map(function ($col) {
            return $this->compiler->compileCol($col, $this->db, $this->table);
        }, is_array($cols) ? $cols : func_get_args());

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
        $cols = array_map(function ($col) {
            return $this->compiler->compileCol($col, $this->db, $this->table);
        }, is_array($cols) ? $cols : [$cols]);

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
     * Get the SQL string.
     *
     * @return string
     */
    public function toSql(): string
    {
        switch ($this->queryType) {
            case static::SELECT:
                return $this->compiler->compileSelect($this->table, $this->parts);
            case static::INSERT:
                return $this->compiler->compileInsert($this->table, $this->parts);
            case static::UPDATE:
                return $this->compiler->compileUpdate($this->table, $this->parts);
            case static::DELETE:
                return $this->compiler->compileDelete($this->table, $this->parts);
        }
    }

    /**
     * Get the string
     * representation of the query.
     *
     * @return string
     */
    public function __toString(): string
    {
        try {
            return $this->toSql();
        } catch (Throwable $e) {
            trigger_error($e->getMessage(), \E_USER_ERROR);
        }
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
