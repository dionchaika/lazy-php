<?php

namespace Lazy\Http;

use Throwable;
use ArrayAccess;
use RuntimeException;
use InvalidArgumentException;

/**
 * The PSR-7 HTTP message header collection class.
 *
 * @see https://www.php-fig.org/psr/psr-7/
 * @see https://tools.ietf.org/html/rfc7230
 */
class Headers implements ArrayAccess
{
    /**
     * The default environments.
     */
    const DEFAULT_ENVIRONMENTS = [

        'HTTP_HOST' => 'localhost'

    ];

    /**
     * The array of all of the headers.
     *
     * Note: The array keys are the normalized
     * header names while the array values contains
     * the original header name and the array of header values.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * The header collection constructor.
     *
     * @param  array  $headers
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $headers = [])
    {
        $this->setHeaders($headers);
    }

    /**
     * Create a new header collection from PHP globals.
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function fromGlobals()
    {
        return static::fromEnvironments($_SERVER);
    }

    /**
     * Create a new header collection from environments.
     *
     * @param  array  $environments
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function fromEnvironments(array $environments)
    {
        $environments = array_merge(static::DEFAULT_ENVIRONMENTS, $environments);

        $headers = new static;

        foreach ($environments as $key => $value) {
            if ('CONTENT_TYPE' === $key) {
                $headers->set('CONTENT-TYPE', $value);
            } else if ('CONTENT_LENGTH' === $key) {
                $headers->set('CONTENT-LENGTH', $value);
            } else if (0 === strpos($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));

                $delim = (0 === strcmp($name, 'COOKIE')) ? ';' : ',';
                $method = (0 === strcmp($name, 'SET-COOKIE')) ? 'add' : 'set';

                $headers->{$method}($name, ('add' === $method) ? trim($value) : array_map('trim', explode($delim, $value)));
            }
        }

        return $headers;
    }

    /**
     * Create a new header collection from string.
     *
     * @param  string  $headers
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function fromString($headers)
    {
        $lines = explode("\r\n", $headers);

        $headers = new static;

        foreach ($lines as $line) {
            if (false === strpos($line, ':')) {
                throw new InvalidArgumentException(
                    "Invalid header: {$line}! "
                    ."Header must be compliant with the \"RFC 7230\" standart."
                );
            }

            [$name, $value] = explode(':', $line, 2);

            $delim = (0 === strcasecmp($name, 'cookie')) ? ';' : ',';
            $method = (0 === strcasecmp($name, 'set-cookie')) ? 'add' : 'set';

            $headers->{$method}($name, ('add' === $method) ? trim($value) : array_map('trim', explode($delim, $value)));
        }

        return $headers;
    }

    /**
     * Get the raw array
     * of all of the headers in the collection.
     *
     * @return array
     */
    public function raw()
    {
        return $this->headers;
    }

    /**
     * Get the array
     * of all of the headers in the collection.
     *
     * @return array
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
        return isset($this->headers[$this->normalizeName($name)]);
    }

    /**
     * Get the header from the collection.
     *
     * @param  string  $name
     * @return string[]
     */
    public function get($name)
    {
        $name = $this->normalizeName($name);

        return isset($this->headers[$name]) ? $this->headers[$name]['value'] : [];
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
     * Set a header to the collection.
     *
     * @param  string  $name
     * @param  string|string[]  $value
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function set($name, $value)
    {
        $name = $this->filterName($name);
        $value = $this->filterValue((array) $value);

        $this->headers[$this->normalizeName($name)] = compact('name', 'value');

        return $this;
    }

    /**
     * Set the headers to the collection.
     *
     * @param  array  $headers
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->set($name, $value);
        }

        return $this;
    }

    /**
     * Add a header to the collection.
     *
     * @param  string  $name
     * @param  string|string[]  $value
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function add($name, $value)
    {
        $name = $this->filterName($name);
        $value = $this->filterValue((array) $value);

        $normalizedName = $this->normalizeName($name);

        if (! isset($this->headers[$normalizedName])) {
            $this->headers[$normalizedName] = compact('name', 'value');
        } else {
            $this->headers[$normalizedName]['value'] = array_merge($this->headers[$normalizedName]['value'], $value);
        }

        return $this;
    }

    /**
     * Add the headers to the collection.
     *
     * @param  array  $headers
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function addHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->add($name, $value);
        }

        return $this;
    }

    /**
     * Remove the header from the collection.
     *
     * @param  string  $name
     * @return $this
     */
    public function remove($name)
    {
        unset($this->headers[$this->normalizeName($name)]);

        return $this;
    }

    /**
     * Get the count of all of the headers in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->headers);
    }

    /**
     * Send all of the headers in the collection to browser.
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function send()
    {
        if (headers_sent()) {
            throw new RuntimeException('Unable to send the headers! Headers are already sent.');
        }

        foreach ($this->all() as $name => $value) {
            if (0 === strcasecmp($name, 'set-cookie')) {
                foreach ($value as $cookie) {
                    header(sprintf("%s: %s", $name, $cookie), false);
                }
            } else {
                header(sprintf("%s: %s", $name, $this->getLine($name)));
            }
        }
    }

    /**
     * Get the header line from the collection.
     *
     * @param  string  $name
     * @return string
     */
    public function __get($name)
    {
        return $this->getLine($name);
    }

    /**
     * Add a header to the collection.
     *
     * @param  string  $name
     * @param  string|string[]  $value
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function __set($name, $value)
    {
        $this->add($name, $value);
    }

    /**
     * Check is the header exists in the collection.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Get the header line from the collection.
     *
     * @param  string  $offset
     * @return string
     */
    public function offsetGet($offset)
    {
        return $this->getLine($offset);
    }

    /**
     * Add a header to the collection.
     *
     * @param  string  $offset
     * @param  string|string[]  $value
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function offsetSet($offset, $value)
    {
        $this->add($offset, $value);
    }

    /**
     * Remove the header from the collection.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * Stringify the header collection.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $str = '';

            foreach ($this->all() as $name => $value) {
                if (0 === strcasecmp($name, 'set-cookie')) {
                    foreach ($value as $cookie) {
                        $str .= sprintf("%s: %s\r\n", $name, $cookie);
                    }
                } else {
                    $value = (0 === strcasecmp($name, 'cookie'))
                        ? implode('; ', $value)
                        : $this->getLine($name);

                    $str .= sprintf("%s: %s\r\n", $name, $value);
                }
            }

            return $str;
        } catch (Throwable $e) {
            trigger_error($e->getMessage(), \E_USER_ERROR);
        }
    }

    /**
     * Normalize a header name.
     *
     * @param  string  $name
     * @return string
     */
    protected function normalizeName($name)
    {
        return implode('-',
                       array_map('ucfirst',
                                 explode('-',
                                         strtolower($name))));
    }

    /**
     * Filter a header name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterName($name)
    {
        if (! preg_match('/^[!#$%&\'*+\-.^_`|~0-9a-zA-Z]+$/', $name)) {
            throw new InvalidArgumentException(
                "Invalid header name: {$name}! "
                ."Header name must be compliant with the \"RFC 7230\" standart."
            );
        }

        return $name;
    }

    /**
     * Filter a header value.
     *
     * @param  string[]  $value
     * @return string[]
     *
     * @throws \InvalidArgumentException
     */
    protected function filterValue(array $value)
    {
        foreach ($value as $val) {
            if (preg_match('/(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))/', $val)) {
                throw new InvalidArgumentException(
                    "Invalid header value: {$val}! "
                    ."Header value must be compliant with the \"RFC 7230\" standart."
                );
            }

            for ($i = 0; $i < strlen($val); $i++) {
                $ascii = ord($val[$i]);

                if ((32 > $ascii && (9 !== $ascii && 10 !== $ascii && 13 !== $ascii)) || 127 === $ascii || 254 < $ascii) {
                    throw new InvalidArgumentException(
                        "Invalid header value: {$val}! "
                        ."Header value must be compliant with the \"RFC 7230\" standart."
                    );
                }
            }
        }

        return $value;
    }
}
