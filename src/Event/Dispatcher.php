<?php

namespace Lazy\Event;

/**
 * The event dispatcher class.
 */
class Dispatcher
{
    /**
     * The array of event listeners.
     *
     * @var mixed[]
     */
    protected $listeners = [];

    /**
     * Add an event listener.
     *
     * @param  string  $event
     * @param  \Closure|mixed  $callback
     * @return self
     */
    public function on(string $event, $callback): self
    {
        $this->listeners[$event][] = $callback;
        return $this;
    }
}
