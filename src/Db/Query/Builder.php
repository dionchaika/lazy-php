<?php

namespace Lazy\Db\Query;

class Builder
{
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
     * The builder constructor.
     *
     * @param  string|null  $db
     * @param  string|null  $table
     */
    public function __construct(?string $db = null, ?string $table = null)
    {
        $this->db = $db;
        $this->table = $table;
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
     * Devide an alias from column.
     *
     * @param  string  $col
     * @return mixed[]
     */
    protected function devideAlias(string $col): array
    {
        $col = explode(' as ', $col, 2);
        return [$col[0], !empty($col[1]) ? $col[1] : null];
    }
}
