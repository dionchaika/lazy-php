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
     * The message header collection.
     *
     * @var \Lazy\Http\Headers
     */
    protected $headers;

    /**
     * The message protocol version.
     *
     * @var string
     */
    protected $protocolVersion = '1.1';

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
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers->all();
    }

    /**
     * Check is the message header exists.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    /**
     * Get the message header.
     *
     * @param  string  $name
     * @return string[]
     */
    public function getHeader($name)
    {
        return $this->headers->get($name);
    }

    /**
     * Get the message header line.
     *
     * @param  string  $name
     * @return string
     */
    public function getHeaderLine($name)
    {
        return $this->headers->getLine($name);
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

        $new->headers->set($name, $value);

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

        $new->headers->add($name, $value);

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

        $new->headers->remove($name);

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
     * Get the message header collection.
     *
     * @return \Lazy\Http\Headers
     */
    public function getHeaderCollection()
    {
        return $this->headers;
    }

    /**
     * Get the message header line.
     *
     * @param  string  $name
     * @return string
     */
    public function __get($name)
    {
        return $this->headers->getLine($name);
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
        $this->headers->add($name, $value);
    }

    /**
     * Check is the message header exists.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->headers->has($offset);
    }

    /**
     * Get the message header line.
     *
     * @param  string  $offset
     * @return string
     */
    public function offsetGet($offset)
    {
        return $this->headers->getLine($offset);
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
        $this->headers->add($offset, $value);
    }

    /**
     * Delete the message header.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->headers->remove($offset);
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
