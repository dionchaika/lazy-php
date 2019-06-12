<?php

namespace Lazy\Db\Query;

use Lazy\Db\Query\Compilers\Compiler as BaseCompiler;

class Builder
{
    use WhereTrait;

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
     * The SQL compiler.
     *
     * @var \Lazy\Db\Query\CompilerInterface
     */
    protected $compiler;

    /**
     * The array of columns.
     *
     * @var string[]
     */
    protected $cols = [];

    /**
     * The array of aliases.
     *
     * @var mixed[]
     */
    protected $aliases = [];

    /**
     * Is the select distinct.
     *
     * @var bool
     */
    protected $distinct = false;

    /**
     * The array of query conditions.
     *
     * @var string[]
     */
    protected $conditions = [];

    /**
     * The builder constructor.
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
