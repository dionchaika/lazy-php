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
     * @var mixed|null
     */
    protected $target;

    /**
     * The event constructor.
     *
     * @param  mixed|null  $target
     */
    public function __construct($target = null)
    {
        $this->target = $target;
    }

    /**
     * Get the event target.
     *
     * @return mixed|null
     */
    public function getTarget()
    {
        return $this->target;
    }
}
