<?php

namespace Lazy\Db\Query;

class Builder
{
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
     * SELECT...
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
     * Devide an alias from column.
     *
     * @param  string  $col
     * @return mixed[]
     */
    protected function devideAlias(string $col): array
    {
        if (preg_match('/^(\w+)(\s+as\s+(\w+))?$/i', $col, $matches)) {
            return [$matches[1], $matches[3]];
        }

        return [$col, null];
    }
}
