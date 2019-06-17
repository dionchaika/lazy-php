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
     * @var string
     */
    public $table;

    /**
     * The array of query values.
     *
     * @var mixed[]
     */
    public $vals = [];

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
     * @param  mixed[]  $vals
     * @return self
     */
    public function insert(array $vals = []): self
    {
        $this->statement = 'Insert';

        $this->vals = $vals;

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
     * @param  string|null  $table
     * @return self
     */
    public function update(?string $table = null): self
    {
        $this->statement = 'Update';

        if (null !== $table) {
            return $this->table($table);
        }

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
     * @param  string|null  $table
     * @return self
     */
    public function delete(?string $table = null): self
    {
        $this->statement = 'Delete';

        if (null !== $table) {
            return $this->table($table);
        }

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
