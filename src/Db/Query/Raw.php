<?php

namespace Lazy\Db\Query;

use Throwable;

/**
 * The raw SQL class.
 */
class Raw
{
    /**
     * The raw SQL value.
     *
     * @var mixed
     */
    protected $val;

    /**
     * The raw SQL constructor.
     *
     * @param  mixed  $val
     */
    public function __construct($val)
    {
        $this->val = $val;
    }

    /**
     * Get the raw SQL value.
     *
     * @return mixed
     */
    public function getVal()
    {
        return $this->val;
    }

    /**
     * Get the string
     * representation of the raw SQL.
     *
     * @return string
     */
    public function __toString(): string
    {
        try {
            return (string) $this->getVal();
        } catch (Throwable $e) {
            trigger_error($e->getMessage(), \E_USER_ERROR);
        }
    }
}
