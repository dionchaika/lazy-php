<?php

namespace Lazy\Event;

/**
 * The event base class.
 */
class Event
{
    /**
     * The event target.
     *
     * @var mixed
     */
    protected $target;

    /**
     * The event constructor.
     *
     * @param  mixed  $target
     */
    public function __construct($target = null)
    {
        $this->target = $target;
    }

    /**
     * Get the event target.
     *
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }
}
