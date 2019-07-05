<?php

namespace Lazy\Http;

use ArrayAccess;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\MessageInterface;

/**
 * The PSR-7 HTTP message implementation class.
 *
 * @see https://www.php-fig.org/psr/psr-7/
 * @see https://tools.ietf.org/html/rfc7230
 */
abstract class Message implements ArrayAccess, MessageInterface
{
    /**
     * The message body.
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $body;

    /**
     * The array of message headers.
     *
     * @var mixed[]
     */
    protected $headers = [];

    /**
     * The message protocol version.
     *
     * @var string
     */
    protected $protocolVersion = '1.1';

    /**
     * Create a new array of headers from globals.
     *
     * @return mixed[]
     */
    public static function fromGlobals()
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $name = implode('-', array_map('ucfirst', explode('-', $name)));

                $delim = (0 === strcasecmp($name, 'cookie')) ? ';' : ',';

                $headers[$name] = array_map('trim', explode($delim, $value));
            }
        }

        return $headers;
    }

    /**
     * Get the message protocol version.
     *
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * Return an instance
     * with the specified message protocol version.
     *
     * @param  string  $version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        $new = clone $this;

        $new->protocolVersion = $version;

        return $new;
    }

    /**
     * Get the array of message headers.
     *
     * @return mixed[]
     */
    public function getHeaders()
    {
        $headers = [];

        foreach ($this->headers as $header) {
            $headers[$header['name']] = $header['values'];
        }

        return $headers;
    }

    /**
     * Check is the message header exists.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasHeader($name)
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * Get the message header.
     *
     * @param  string  $name
     * @return string[]
     */
    public function getHeader($name)
    {
        $name = strtolower($name);

        return isset($this->headers[$name]) ? $this->headers[$name]['values'] : [];
    }

    /**
     * Get the message header line.
     *
     * @param  string  $name
     * @return string
     */
    public function getHeaderLine($name)
    {
        $name = strtolower($name);

        return isset($this->headers[$name]) ? implode(', ', $this->headers[$name]['values']) : '';
    }

    /**
     * Return an instance
     * with the specified message header.
     *
     * @param  string  $name
     * @param  string|string[]  $value
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withHeader($name, $value)
    {
        $new = clone $this;

        $new->setHeader($name, $value);

        return $new;
    }

    /**
     * Return an instance
     * with the specified message
     * header appended with the given value.
     *
     * @param  string  $name
     * @param  string|string[]  $value
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withAddedHeader($name, $value)
    {
        $new = clone $this;

        $new->addHeader($name, $value);

        return $new;
    }

    /**
     * Return an instance
     * without the specified message header.
     *
     * @param  string  $name
     * @return static
     */
    public function withoutHeader($name)
    {
        $new = clone $this;

        $new->deleteHeader($name);

        return $new;
    }

    /**
     * Get the message body.
     *
     * @return \Psr\Http\Message\StreamInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getBody()
    {
        if (! $this->body) {
            $this->body = create_stream();
        }

        return $this->body;
    }

    /**
     * Return an instance
     * with the specified message body.
     *
     * @param  \Psr\Http\Message\StreamInterface  $body
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withBody(StreamInterface $body)
    {
        $new = clone $this;

        $new->body = $new->filterBody($body);

        return $new;
    }

    /**
     * Set the message header.
     *
     * @param  string  $name
     * @param  string|string[]  $value
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function setHeader($name, $value)
    {
        $this->headers[strtolower($name)] = [

            'name'   => $this->filterHeaderName($name),
            'values' => $this->filterHeaderValue($value)

        ];

        return $this;
    }

    /**
     * Set the message headers.
     *
     * @param  mixed[]  $headers
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        return $this;
    }

    /**
     * Add the message header.
     *
     * @param  string  $name
     * @param  string|string[]  $value
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function addHeader($name, $value)
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
     * Add the message headers.
     *
     * @param  mixed[]  $headers
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function addHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }

        return $this;
    }

    /**
     * Get the message header line.
     *
     * @param  string  $name
     * @return string
     */
    public function __get($name)
    {
        return $this->getHeaderLine($name);
    }

    /**
     * Add the message header.
     *
     * @param  string  $name
     * @param  string|string[]  $value
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function __set($name, $value)
    {
        $this->addHeader($name, $value);
    }

    /**
     * Check is the message header exists.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->hasHeader($offset);
    }

    /**
     * Get the message header line.
     *
     * @param  string  $offset
     * @return string
     */
    public function offsetGet($offset)
    {
        return $this->getHeaderLine($offset);
    }

    /**
     * Add the message header.
     *
     * @param  string  $offset
     * @param  string|string[]  $value
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function offsetSet($offset, $value)
    {
        $this->addHeader($offset, $value);
    }

    /**
     * Delete the message header.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->deleteHeader($offset);
    }

    /**
     * Delete the message header.
     *
     * @param  string  $name
     * @return void
     */
    protected function deleteHeader($name)
    {
        unset($this->headers[strtolower($name)]);
    }

    /**
     * Filter a message header name.
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
     * Filter a message header value.
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

    /**
     * Filter a message body.
     *
     * @param  Psr\Http\Message\StreamInterface  $body
     * @return Psr\Http\Message\StreamInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function filterBody(StreamInterface $body)
    {
        if (! $body->isReadable()) {
            throw new InvalidArgumentException('Invalid body! Body is not readable.');
        }

        return $body;
    }
}
