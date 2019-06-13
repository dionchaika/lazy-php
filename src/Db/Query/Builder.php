<?php

namespace Lazy\Db\Query;

use Throwable;
use Lazy\Db\Query\Compilers\Compiler as BaseCompiler;

/**
 * The query builder class.
 */
class Builder
{
    use JoinTrait,
        WhereTrait,
        OrderByTrait;

    /**
     * The query types.
     */
    const QUERY_TYPES = [

        'select' => 0,
        'insert' => 1,
        'update' => 2,
        'delete' => 3

    ];

    /**
     * The current query type.
     *
     * @var int
     */
    protected $queryType = self::QUERY_TYPES['select'];

    /**
     * The query DB.
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
     * The query compiler.
     *
     * @var \Lazy\Db\Query\CompilerInterface
     */
    protected $compiler;

    /**
     * The array of query columns.
     *
     * @var string[]
     */
    protected $cols = [];

    /**
     * The array of query aliases.
     *
     * @var mixed[]
     */
    protected $aliases = [];

    /**
     * Is the select query distinct.
     *
     * @var bool
     */
    protected $distinct = false;

    /**
     * The query builder constructor.
     *
     * @param  string|null  $db
     * @param  string|null  $table
     * @param  \Lazy\Db\Query\CompilerInterface|null  $compiler
     */
    public function __construct(?string $db = null, ?string $table = null, ?CompilerInterface $compiler = null)
    {
        $this->db = $db;
        $this->table = $table;
        $this->compiler = $compiler ?? new BaseCompiler;
    }

    /**
     * select...
     *
     * @param  mixed  $cols
     * @return self
     */
    public function select($cols = '*'): self
    {
        $this->queryType = static::QUERY_TYPES['select'];

        $cols = is_array($cols)
            ? $cols
            : func_get_args();

        foreach ($cols as $col) {
            [$col, $alias] = $this->devideAlias($col);

            $this->cols[] = $col;
            if ($alias) {
                $this->aliases[$col] = $alias;
            }
        }

        return $this;
    }

    /**
     * distinct...
     *
     * @return self
     */
    public function distinct(): self
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * from...
     *
     * @param  string  $table
     * @return self
     */
    public function from(string $table): self
    {
        return $this->setTable($table);
    }

    /**
     * delete...
     *
     * @return self
     */
    public function delete(): self
    {
        $this->queryType = static::QUERY_TYPES['delete'];
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
            case static::QUERY_TYPES['select']:
                return $this->compiler->compileSelect(
                    $this->db,
                    $this->table,
                    $this->cols,
                    $this->aliases,
                    $this->distinct,
                    $this->joins,
                    $this->wheres,
                    $this->ordersBy
                );
            case static::QUERY_TYPES['delete']:
                return $this->compiler->compileDelete(
                    $this->db,
                    $this->table,
                    $this->aliases,
                    $this->wheres
                );
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
     * Set the query table.
     *
     * @param  string  $table
     * @return self
     */
    protected function setTable(string $table): self
    {
        [$table, $alias] = $this->devideAlias($table);

        $this->table = $table;
        if ($alias) {
            $this->aliases[$table] = $alias;
        }

        return $this;
    }

    /**
     * Devide an alias from the name.
     *
     * @param  string  $name
     * @return mixed[]
     */
    protected function devideAlias(string $name): array
    {
        $name = explode(' as ', $name, 2);
        return [$name[0], !empty($name[1]) ? $name[1] : null];
    }
}
