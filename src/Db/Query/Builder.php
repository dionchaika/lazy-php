<?php

namespace Lazy\Db\Query;

use Closure;
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
     * The array of query where clauses.
     *
     * @var mixed[]
     */
    public $wheres = [];

    /**
     * The array of query order by clauses.
     *
     * @var mixed[]
     */
    public $ordersBy = [];

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
     * where...
     *
     * @param  mixed  $column
     * @param  mixed|null  $operator
     * @param  mixed|null  $value
     * @param  string  $delimiter
     * @param  bool  $negative
     * @return self
     */
    public function where($column, $operator = null, $value = null, string $delimiter = 'and', bool $negative = false): self
    {
        if ($column instanceof Closure) {
            return $this->whereGroup($column);
        }

        if (1 === func_num_args()) {
            return $this->whereIsNot($column, null);
        }

        [$value, $operator] = $this->prepareValueAndOperator($value, $operator);

        if ((null === $value || is_bool($value)) && '=' === $operator) {
            return $this->whereIs($column, $value, $delimiter, $negative);
        }

        if ($value instanceof Closure) {
            return $this->whereSelect($column, $operator, $value, $delimiter, $negative);
        }

        $type = 'Simple';
        $this->wheres[] = compact('type', 'column', 'operator', 'value', 'delimiter', 'negative');

        return $this;
    }

    /**
     * where is...
     *
     * @param  string  $column
     * @param  mixed  $value
     * @param  string  $delimiter
     * @param  bool  $negative
     * @return self
     */
    public function whereIs(string $column, $value, string $delimiter = 'and', bool $negative = false): self
    {
        $type = 'Is';
        $this->wheres[] = compact('type', 'column', 'value', 'delimiter', 'negative');

        return $this;
    }

    /**
     * where is not...
     *
     * @param  string  $column
     * @param  mixed  $value
     * @param  string  $delimiter
     * @return self
     */
    public function whereIsNot(string $column, $value, string $delimiter = 'and'): self
    {
        return $this->whereIs($column, $value, $delimiter, true);
    }

    /**
     * where ( ... ) ...
     *
     * @param  \Closure  $callback
     * @param  string  $delimiter
     * @param  bool  $negative
     * @return self
     */
    public function whereGroup(Closure $callback, string $delimiter = 'and', bool $negative = false): self
    {
        $callback($query = $this->getSubSelectQuery());

        $type = 'Group';
        $this->wheres[] = compact('type', 'query', 'delimiter', 'negative');

        return $this;
    }

    /**
     * where ( select ... ) ...
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  \Closure  $callback
     * @param  string  $delimiter
     * @param  bool  $negative
     * @return self
     */
    public function whereSelect(string $column, string $operator, Closure $callback, string $delimiter = 'and', bool $negative = false): self
    {
        $callback($query = $this->getSubSelectQuery());

        $type = 'Select';
        $this->wheres[] = compact('type', 'column', 'operator', 'query', 'delimiter', 'negative');

        return $this;
    }

    /**
     * order by...
     *
     * @param  string|string[]  $columns
     * @param  string  $order
     * @return self
     */
    public function orderBy($columns, $order = 'asc'): self
    {
        $this->ordersBy[] = [

            'Order'  => $order,
            'Columns' => is_array($columns) ? $columns : [$columns]

        ];

        return $this;
    }

    /**
     * order by asc...
     *
     * @param  mixed  $columns
     * @return self
     */
    public function orderByAsc($columns): self
    {
        return $this->orderBy(is_array($columns) ? $columns : func_get_args(), 'asc');
    }

    /**
     * order by desc...
     *
     * @param  mixed  $columns
     * @return self
     */
    public function orderByDesc($columns): self
    {
        return $this->orderBy(is_array($columns) ? $columns : func_get_args(), 'desc');
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
     * Get the sub-select query.
     *
     * @return \Lazy\Db\Query\Builder
     */
    protected function getSubSelectQuery(): Builder
    {
        return (new static($this->compiler))->from($this->table);
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
