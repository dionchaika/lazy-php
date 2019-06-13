<?php

namespace Lazy\Db\Query;

/**
 * The raw query class.
 */
class Raw
{
    /**
     * The raw query value.
     *
     * @var string
     */
    protected $val;

    /**
     * The raw query constructor.
     *
     * @param  string  $val
     */
    public function __construct(string $val)
    {
        $this->val = $val;
    }

    /**
     * Get the raw query value.
     *
     * @return string
     */
    public function getVal(): string
    {
        return $this->val;
    }

    /**
     * Get the string
     * representation of the raw query.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getVal();
    }
}
