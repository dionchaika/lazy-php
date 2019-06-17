<?php

namespace Lazy\Db\Query;

use Throwable;
use Lazy\Db\Query\Compilers\Compiler as BaseCompiler;

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
    public $table;

    /**
     * The query database.
     *
     * @var string
     */
    public $database;

    /**
     * The array of query columns.
     *
     * @var mixed[]
     */
    public $columns = [];

    /**
     * Is the query select statement distinct.
     *
     * @var bool
     */
    public $distinct = false;

    /**
     * The query compiler.
     *
     * @var \Lazy\Db\Query\CompilerInterface
     */
    protected $compiler;

    /**
     * The current query statement.
     *
     * @var string
     */
    protected $statement = 'Select';

    /**
     * The query builder constructor.
     *
     * @param  \Lazy\Db\Query\CompilerInterface|null  $compiler
     */
    public function __construct(?CompilerInterface $compiler = null)
    {
        $this->compiler = $compiler ?? new BaseCompiler;
    }

    /**
     * select...
     *
     * @param  mixed  $columns
     * @return self
     */
    public function select($columns = '*'): self
    {
        $this->statement = 'Select';

        $columns = is_array($columns)
            ? $columns
            : func_get_args();

        $this->columns = array_merge($this->columns, $columns);

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
     * @param  mixed  $table
     * @return self
     */
    public function from($table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Get the SQL.
     *
     * @return string
     */
    public function toSql(): string
    {
        return $this->compiler->{'compile'.$this->statement}($this);
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
     * Prepare a value and an operator.
     *
     * @param  mixed  $value
     * @param  mixed  $operator
     * @return mixed[]
     */
    protected function prepareValueAndOperator($value, $operator): array
    {
        return (null === $value) ? [$operator, '='] : [$value, $operator];
    }
}
