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
    public $target;

    /**
     * Enable the event propagation.
     *
     * @var bool
     */
    public $propagation = true;

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
     * Stop the event propagation.
     *
     * @return void
     */
    public function stopPropagation(): void
    {
        $this->propagation = false;
    }
}
