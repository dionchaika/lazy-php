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
     * The array of query values.
     *
     * @var mixed[]
     */
    protected $vals = [];

    /**
     * Is the select query distinct.
     *
     * @var bool
     */
    protected $distinct = false;

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
     * Get the raw SQL.
     *
     * @param  mixed  $val
     * @return \Lazy\Db\Query\Raw
     */
    public static function raw($val): Raw
    {
        return new Raw($val);
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
     * insert...
     *
     * @return self
     */
    public function insert(): self
    {
        $this->statement = 'Insert';
        return $this;
    }

    /**
     * into...
     *
     * @param  string  $table
     * @return self
     */
    public function into(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * values...
     *
     * @param  mixed[]  $vals
     * @return self
     */
    public function values(array $vals): self
    {
        $this->vals = $vals;
        return $this;
    }

    /**
     * update...
     *
     * @return self
     */
    public function update(): self
    {
        $this->statement = 'Update';
        return $this;
    }

    /**
     * table...
     *
     * @param  string  $table
     * @return self
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * set...
     *
     * @param  mixed[]  $vals
     * @return self
     */
    public function set(array $vals): self
    {
        $this->vals = $vals;
        return $this;
    }

    /**
     * delete...
     *
     * @return self
     */
    public function delete(): self
    {
        $this->statement = 'Delete';
        return $this;
    }

    /**
     * Get the SQL.
     *
     * @return string
     */
    public function toSql(): string
    {
        return $this->compiler->{'compile'.$this->statement}();
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
