<?php

namespace Lazy\Event;

use Closure;

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

    /**
     * Emit the event.
     *
     * @param  string  $event
     * @param  mixed  $params
     * @return self
     */
    public function emit(string $event, ...$params): self
    {
        if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $listener) {
                if ($listener instanceof Closure) {
                    $listener($event, ...$params);
                }
            }
        }

        return $this;
    }
}
