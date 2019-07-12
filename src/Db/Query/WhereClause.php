<?php

namespace Lazy\Db\Query;

/**
 * The query where clause builder class.
 */
class WhereClause
{
    /**
     * The array of query where clause parts.
     *
     * @var array
     */
    protected $parts = [];

    /**
     * equal...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function equal($column, $value)
    {
        //
    }

    /**
     * not equal...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function notEqual($column, $value)
    {
        //
    }

    /**
     * or equal...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function orEqual($column, $value)
    {
        //
    }

    /**
     * or not equal...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function orNotEqual($column, $value)
    {
        //
    }

    /**
     * less than...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function lessThan($column, $value)
    {
        //
    }

    /**
     * or less than...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function orLessThan($column, $value)
    {
        //
    }

    /**
     * less than or equal...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function lessThanOrEqual($column, $value)
    {
        //
    }

    /**
     * or less than or equal...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function orLessThanOrEqual($column, $value)
    {
        //
    }

    /**
     * greater than...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function greaterThan($column, $value)
    {
        //
    }

    /**
     * or greater than...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function orGreaterThan($column, $value)
    {
        //
    }

    /**
     * greater than or equal...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function greaterThanOrEqual($column, $value)
    {
        //
    }

    /**
     * or greater than or equal...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function orGreaterThanOrEqual($column, $value)
    {
        //
    }

    /**
     * like...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function like($column, $value)
    {
        //
    }

    /**
     * not like...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function notLike($column, $value)
    {
        //
    }

    /**
     * or like...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function orLike($column, $value)
    {
        //
    }

    /**
     * or not like...
     *
     * @param  mixed  $column
     * @param  mixed  $value
     * @return $this
     */
    public function orNotLike($column, $value)
    {
        //
    }

    //

    /**
     * in...
     *
     * @param  mixed  $column
     * @param  array  $values
     * @return $this
     */
    public function in($column, array $values)
    {
        //
    }

    /**
     * not in...
     *
     * @param  mixed  $column
     * @param  array  $values
     * @return $this
     */
    public function notIn($column, array $values)
    {
        //
    }

    /**
     * or in...
     *
     * @param  mixed  $column
     * @param  array  $values
     * @return $this
     */
    public function orIn($column, array $values)
    {
        //
    }

    /**
     * or not in...
     *
     * @param  mixed  $column
     * @param  array  $values
     * @return $this
     */
    public function orNotIn($column, array $values)
    {
        //
    }

    /**
     * between...
     *
     * @param  mixed  $column
     * @param  mixed  $minValue
     * @param  mixed  $maxValue
     * @return $this
     */
    public function between($column, $minValue, $maxValue)
    {
        //
    }

    /**
     * not between...
     *
     * @param  mixed  $column
     * @param  mixed  $minValue
     * @param  mixed  $maxValue
     * @return $this
     */
    public function notBetween($column, $minValue, $maxValue)
    {
        //
    }

    /**
     * or between...
     *
     * @param  mixed  $column
     * @param  mixed  $minValue
     * @param  mixed  $maxValue
     * @return $this
     */
    public function orBetween($column, $minValue, $maxValue)
    {
        //
    }

    /**
     * or not between...
     *
     * @param  mixed  $column
     * @param  mixed  $minValue
     * @param  mixed  $maxValue
     * @return $this
     */
    public function orNotBetween($column, $minValue, $maxValue)
    {
        //
    }
}
