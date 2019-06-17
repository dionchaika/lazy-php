<?php

namespace Lazy\Db\Query\Compilers;

use RuntimeException;
use Lazy\Db\Query\Raw;
use Lazy\Db\Query\Builder;
use Lazy\Db\Query\CompilerInterface;

/**
 * The query compiler base class.
 */
class Compiler implements CompilerInterface
{
    /**
     * {@inheritDoc}
     */
    public function compileSelect(Builder $query): string
    {
        if (! $query->table) {
            throw new RuntimeException('Invalid query! Table is not defined.');
        }

        $sql = $query->distinct ? 'select distinct' : 'select';

        $sql .= $this->compileCols($query);
        $sql .= $this->compileFrom($query);
        $sql .= $this->compileWhere($query);

        return $sql.';';
    }

    /**
     * Compile a query value.
     *
     * @param  mixed  $val
     * @return string
     */
    protected function compileVal($val): string
    {
        if (null === $val) {
            return 'null';
        }

        if (true === $val) {
            return 'true';
        }

        if (false === $val) {
            return 'false';
        }

        if (is_numeric($val)) {
            return (string) $val;
        }

        return '\''.addcslashes($val, '\'').'\'';
    }

    /**
     * Compile query columns.
     *
     * @param  \Lazy\Db\Query\Builder  $query
     * @return string
     */
    protected function compileCols(Builder $query): string
    {
        $cols = $query->cols;

        if (empty($cols)) {
            $cols[] = '*';
        }

        $sql = '';
        foreach ($cols as $col) {
            if ($col instanceof Raw) {
                $col = (string) $col;
            }

            $sql .= $col.', ';
        }

        return ' '.rtrim($sql, ', ');
    }

    /**
     * Compile a query from clause.
     *
     * @param  \Lazy\Db\Query\Builder  $query
     * @return string
     */
    protected function compileFrom(Builder $query): string
    {
        return ' from '.(($query->table instanceof Raw) ? (string) $query->table : $query->table);
    }

    /**
     * Compile a query where clause.
     *
     * @param  \Lazy\Db\Query\Builder  $query
     * @return string
     */
    protected function compileWhere(Builder $query): string
    {
        if (empty($query->wheres)) {
            return '';
        }

        $firstDelim = $query->wheres[0]['delim'];

        $sql = '';
        foreach ($query->wheres as $where) {
            $sql .= $where['delim'].' '.$this->{'compileWhere'.$where['type']}($query);
        }

        return ' where '.substr($sql, strlen($firstDelim) + 1);
    }

    /**
     * Compile a query where clause simple expression.
     *
     * @param  mixed[]  $where
     * @return string
     */
    protected function compileWhereSimple(array $where): string
    {
        return $where['col'].' '.$where['op'].' '.$this->compileVal($where['val']);
    }

    /**
     * Compile a query where clause simple negative expression.
     *
     * @param  mixed[]  $where
     * @return string
     */
    protected function compileWhereSimpleNot(array $where): string
    {
        return 'not '.$this->compileWhereSimple($where);
    }

    /**
     * Compile a query where clause is expression.
     *
     * @param  mixed[]  $where
     * @return string
     */
    protected function compileWhereIs(array $where): string
    {
        return $where['col'].' is '.$this->compileVal($where['val']);
    }

    /**
     * Compile a query where clause is not expression.
     *
     * @param  mixed[]  $where
     * @return string
     */
    protected function compileWhereIsNot(array $where): string
    {
        return $where['col'].' is not '.$this->compileVal($where['val']);
    }

    /**
     * Compile a group of query where clause expressions.
     *
     * @param  mixed[]  $where
     * @return string
     */
    protected function compileWhereGroup(array $where): string
    {
        return '('.$this->compileWhere($where['query']).')';
    }

    /**
     * Compile a negative group of query where clause expressions.
     *
     * @param  mixed[]  $where
     * @return string
     */
    protected function compileWhereGroupNot(array $where): string
    {
        return 'not '.$this->compileWhereGroup($where);
    }

    /**
     * Compile a query where clause select expression.
     *
     * @param  mixed[]  $where
     * @return string
     */
    protected function compileWhereSelect(array $where): string
    {
        return '('.$where['query']->toSql().')';
    }

    /**
     * Compile a query where clause negative select expression.
     *
     * @param  mixed[]  $where
     * @return string
     */
    protected function compileWhereSelectNot(array $where): string
    {
        return 'not '.$this->compileWhereSelect($where);
    }
}
