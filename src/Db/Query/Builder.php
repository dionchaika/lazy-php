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
     * The query DB.
     *
     * @var string
     */
    public $db;

    /**
     * The query table.
     *
     * @var string
     */
    public $table;

    /**
     * The array of query columns.
     *
     * @var string[]
     */
    public $cols = [];

    /**
     * Is the select query distinct.
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
     * The current query type.
     *
     * @var int
     */
    protected $statement = 'Select';

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
        $this->statement = 'Select';

        $this->cols = is_array($cols)
            ? $cols
            : func_get_args();

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
}
