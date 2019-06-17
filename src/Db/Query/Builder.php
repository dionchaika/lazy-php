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
        UnionTrait,
        HavingTrait,
        GroupByTrait,
        OrderByTrait;

    /**
     * The query table.
     *
     * @var mixed
     */
    public $table;

    /**
     * The query database.
     *
     * @var mixed
     */
    public $database;

    /**
     * The array of query columns.
     *
     * @var mixed[]
     */
    public $cols = [];

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
     * @param  mixed  $cols
     * @return self
     */
    public function select($cols = '*'): self
    {
        $this->statement = 'Select';

        $cols = is_array($cols)
            ? $cols
            : func_get_args();

        $this->cols = array_merge($this->cols, $cols);

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
     * Prepare an operator and a value.
     *
     * @param  mixed  $op
     * @param  mixed  $val
     * @return mixed[]
     */
    protected function prepareOpAndVal($op, $val): array
    {
        return (null === $val) ? ['=', $op] : [$op, $val];
    }
}
