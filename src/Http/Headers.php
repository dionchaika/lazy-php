<?php

namespace Lazy\Http;

class Headers
{
    protected $headers = [];

    public function all()
    {
        foreach ($this->headers as $header) {
            $headers[$header['name']] = $header['value'];
        }

        return $headers;
    }

    public function has($name)
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function get($name)
    {
        return $this->has($name) ? $this->headers[strtolower($name)] : [];
    }

    public function getLine($name)
    {
        return implode(', ', $this->get($name));
    }

    public function set($name, $value)
    {
        $value = (array) $value;

        $this->headers[strtolower($name)] = compact('name', 'value');

        return $this;
    }

    public function add($name, $value)
    {
        if (! $this->has($name)) {
            $this->set($name, $value);
        } else {
            $this->headers[strtolower($name)]['value'] = array_merge(
                $this->headers[strtolower($name)]['value'], (array) $value
            );
        }

        return $this;
    }

    public function remove($name)
    {
        unset($this->headers[strtolower($name)]);
    }
}
