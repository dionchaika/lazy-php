<?php

namespace Lazy\Http;

class Headers
{
    /**
     * The array of all of the headers.
     *
     * Note: The array keys
     * are the lowercase header names while
     * the array values contains the original
     * header name and the array of header values.
     *
     * @var mixed[]
     */
    protected $headers = [];

    /**
     * Get the raw array
     * of all of the headers in the collection.
     *
     * @return mixed[]
     */
    public function raw()
    {
        return $this->headers;
    }

    /**
     * Get the array
     * of all of the headers in the collection.
     *
     * @return mixed[]
     */
    public function all()
    {
        foreach ($this->headers as $header) {
            $headers[$header['name']] = $header['value'];
        }

        return $headers;
    }

    /**
     * Check is the header exists in the collection.
     *
     * @param  string  $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * Get the header from the collection.
     *
     * @param  string  $name
     * @return string[]
     */
    public function get($name)
    {
        $name = strtolower($name);

        return isset($this->headers[$name]) ? $this->headers[$name] : [];
    }

    /**
     * Get the header line from the collection.
     *
     * @param  string  $name
     * @return string
     */
    public function getLine($name)
    {
        return implode(', ', $this->get($name));
    }

    /**
     * Set header in the collection.
     *
     * @param  string  $name
     * @param  string|string[]  $value
     * @return self
     */
    public function set($name, $value)
    {
        $value = (array) $value;

        $this->headers[strtolower($name)] = compact('name', 'value');

        return $this;
    }

    /**
     * Add header to the collection.
     *
     * @param  string  $name
     * @param  string|string[]  $value
     * @return self
     */
    public function add($name, $value)
    {
        $normalizedName = strtolower($name);

        if (! isset($this->headers[$normalizedName])) {
            $this->headers[$normalizedName] = compact('name', 'value');
        }

        $this->headers[$normalizedName] = array_merge($this->headers[$normalizedName], (array) $value);
    }

    /**
     * Remove header from the collection.
     *
     * @param  string  $name
     * @return void
     */
    public function remove($name)
    {
        unset($this->headers[strtolower($name)]);
    }
}
