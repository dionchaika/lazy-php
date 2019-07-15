<?php

namespace Lazy\Db\Query;

use Closure;
use Lazy\Db\ConnectionInterface;
use Lazy\Db\Query\Compilers\CompilerInterface;

/**
 * The query builder class.
 */
class Builder
{
    /**
     * The query table.
     *
     * @var mixed
     */
    protected $table;

    /**
     * The array of query columns.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * The array of query clauses.
     *
     * @var array
     */
    protected $clauses = [

        'join'    => null,
        'where'   => null,
        'groupBy' => null,
        'having'  => null,
        'orderBy' => null

    ];

    /**
     * Is the select query distinct.
     *
     * @var bool
     */
    protected $distinct = false;

    /**
     * The query SQL compiler.
     *
     * @var Lazy\Db\Query\Compilers\CompilerInterface
     */
    protected $compiler;

    /**
     * The query
     * database connection.
     *
     * @var \Lazy\Db\ConnectionInterface
     */
    protected $connection;

    /**
     * The query constructor.
     *
     * @param  Lazy\Db\Query\Compilers\CompilerInterface  $compiler  The query SQL compiler.
     * @param  \Lazy\Db\ConnectionInterface  $connection  The query database connection.
     */
    public function __construct(CompilerInterface $compiler, ConnectionInterface $connection)
    {
        $this->compiler = $compiler;
        $this->connection = $connection;
    }

    /**
     * select...
     *
     * @param  array|mixed  $columns
     * @return $this
     */
    public function select($columns = ['*'])
    {
        $this->columns = array_merge(
            $this->columns, is_array($columns) ? $columns : func_get_args()
        );

        return $this;
    }

    /**
     * from...
     *
     * @param  mixed  $table
     * @return $this
     */
    public function from($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * distinct...
     *
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * where...
     *
     * @param  \Closure  $callback
     * @return $this
     */
    public function where(Closure $callback)
    {
        $where = new WhereClause();

        $this->clauses['where'] = $callback($where, $this);

        return $this;
    }

    /**
     * Execute a select statement.
     *
     * Note: An alias method name to "get".
     *
     * @return array
     */
    public function all()
    {
        return $this->get();
    }

    /**
     * Execute a select statement.
     *
     * @return array
     */
    public function get()
    {
        return $this->connection->select(
            $this->compiler->compileSelect($this->table, $this->columns, $this->clauses, $this->distinct)
        );
    }

    /**
     * Execute a select statement and return the first row.
     *
     * @return mixed
     */
    public function first()
    {
        return $this->connection->selectFirst(
            $this->compiler->compileSelect($this->table, $this->columns, $this->clauses, $this->distinct)
        );
    }

    /**
     * Execute a select statement and return the last row.
     *
     * @return mixed
     */
    public function last()
    {
        return $this->connection->selectLast(
            $this->compiler->compileSelect($this->table, $this->columns, $this->clauses, $this->distinct)
        );
    }

    /**
     * Execute a select statement and return the random row.
     *
     * @return mixed
     */
    public function rand()
    {
        return $this->connection->selectRand(
            $this->compiler->compileSelect($this->table, $this->columns, $this->clauses, $this->distinct)
        );
    }
}
