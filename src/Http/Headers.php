<?php

namespace Lazy\Http;

use InvalidArgumentException;

/**
 * The PSR-7 HTTP message
 * headers collection class.
 *
 * @see https://www.php-fig.org/psr/psr-7/
 * @see https://tools.ietf.org/html/rfc7230
 */
class Headers
{
    /**
     * The array of headers.
     *
     * @var mixed[]
     */
    protected $headers = [];

    /**
     * The headers collection constructor.
     *
     * @param  mixed[]  $headers
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $headers = [])
    {
        foreach ($headers as $name => $value) {
            $this->set($name, $value);
        }
    }

    /**
     * Create a new headers collection from globals.
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public static function fromGlobals()
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $name = $this->normilizeName(substr($key, 5));

                $delim = (0 === strcasecmp($name, 'cookie')) ? ';' : ',';

                $value = (0 === strcasecmp($name, 'set-cookie'))
                    ? $value
                    : array_map('trim', explode($delim, $$value));

                $headers[$name] = $value;
            }
        }

        return new static($headers);
    }

    /**
     * Get the array of all of the headers in the collection.
     *
     * @return mixed[]
     */
    public function all()
    {
        $headers = [];

        foreach ($this->headers as $header) {
            $headers[$header['name']] = $header['values'];
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

        return isset($this->headers[$name]) ? $this->headers[$name]['values'] : [];
    }

    /**
     * Get the header line from the collection.
     *
     * @param  string  $name
     * @return string
     */
    public function getLine($name)
    {
        $name = strtolower($name);

        return isset($this->headers[$name]) ? implode(', ', $this->headers[$name]['values']) : '';
    }

    /**
     * Set the header in the collection.
     *
     * @param  string  $name
     * @param  string|string[]  $value
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function set($name, $value)
    {
        $this->headers[strtolower($name)] = [

            'name'   => $this->filterHeaderName($name),
            'values' => $this->filterHeaderValue($value)

        ];

        return $this;
    }

    /**
     * Add the header to the collection.
     *
     * @param  string  $name
     * @param  string|string[]  $value
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function add($name, $value)
    {
        $normalizedName = strtolower($name);

        if (! isset($this->headers[$normalizedName])) {
            $this->headers[$normalizedName] = [

                'name'   => $this->filterHeaderName($name),
                'values' => []

            ];
        }

        $this->headers[$normalizedName]['values'] = array_merge(
            $this->headers[$normalizedName]['values'], $this->filterHeaderValue($value)
        );

        return $this;
    }

    /**
     * Remove a header from the collection.
     *
     * @param  string  $name
     * @return void
     */
    public function remove($name)
    {
        unset($this->headers[strtolower($name)]);
    }

    /**
     * Normalize a header name.
     *
     * @param  string  $name
     * @return string
     */
    protected function normilizeName($name)
    {
        $name = strtolower(str_replace('_', '-', $name));

        return implode('-', array_map('ucfirst', explode('-', $name)));
    }

    /**
     * Filter a header name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterHeaderName($name)
    {
        if (! preg_match('/^[!#$%&\'*+\-.^_`|~0-9a-zA-Z]+$/', $name)) {
            throw new InvalidArgumentException('Invalid header name! Header name must be compliant with the "RFC 7230" standart.');
        }

        return $name;
    }

    /**
     * Filter a header value.
     *
     * @param  string|string[]  $value
     * @return string[]
     *
     * @throws \InvalidArgumentException
     */
    protected function filterHeaderValue($value)
    {
        $values = (array) $value;

        foreach ($values as $value) {
            if (preg_match('/(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))/', $value)) {
                throw new InvalidArgumentException('Invalid header value! Header value must be compliant with the "RFC 7230" standart.');
            }

            for ($i = 0; $i < strlen($value); $i++) {
                $ascii = ord($value[$i]);

                if ((32 > $ascii && (9 !== $ascii && 10 !== $ascii && 13 !== $ascii)) || 127 === $ascii || 254 < $ascii) {
                    throw new InvalidArgumentException('Invalid header value! Header value must be compliant with the "RFC 7230" standart.');
                }
            }
        }

        return $values;
    }
}
