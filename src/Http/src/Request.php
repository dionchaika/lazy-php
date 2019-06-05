<?php

namespace Lazy\Http;

use Throwable;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;

/**
 * The PSR-7 HTTP request message implementation class.
 *
 * @see https://www.php-fig.org/psr/psr-7/
 * @see https://tools.ietf.org/html/rfc7230
 */
class Request extends Message implements RequestInterface
{
    /**
     * The request target.
     *
     * @var string
     */
    protected $requestTarget;

    /**
     * The request method.
     *
     * @var string
     */
    protected $method = Method::GET;

    /**
     * The request URI.
     *
     * @var \Psr\Http\Message\UriInterface
     */
    protected $uri;

    /**
     * The request constructor.
     *
     * @param  string  $method
     * @param  \Psr\Http\Message\UriInterface|string|null  $uri
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     */
    public function __construct($method = Method::GET, $uri = null, $headers = [], $protocolVersion = '1.1')
    {
        $this->method = $this->filterMethod($method);

        if (null === $uri) {
            $uri = new Uri;
        } else if (is_string($uri)) {
            $uri = new Uri($uri);
        }

        $this->uri = $uri;

        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        if ('1.1' === $this->protocolVersion && ! $this->hasHeader('Host')) {
            $this->setHostHeader();
        }

        $this->protocolVersion = $protocolVersion;
    }

    /**
     * Get the request target.
     *
     * @return string
     */
    public function getRequestTarget()
    {
        if (null !== $this->requestTarget && '' !== $this->requestTarget) {
            return $this->requestTarget;
        }

        if (null !== $this->uri) {
            $requestTarget = $this->uri->getPath();
            if ('' === $requestTarget) {
                $requestTarget = '/';
            }

            $query = $this->uri->getQuery();
            if ('' !== $query) {
                $requestTarget .= '?'.$query;
            }

            return $requestTarget;
        }

        return '/';
    }

    /**
     * Return an instance
     * with the specified request target.
     *
     * @param  mixed  $requestTarget
     *
     * @return static
     */
    public function withRequestTarget($requestTarget)
    {
        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * Get the request method.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Return an instance
     * with the specified request method.
     *
     * @param  string  $method
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withMethod($method)
    {
        $new = clone $this;
        $new->method = $new->filterMethod($method);

        return $new;
    }

    /**
     * Get the request URI.
     *
     * @return \Psr\Http\Message\UriInterface
     */
    public function getUri()
    {
        if (null === $this->uri) {
            $this->uri = new Uri;
        }

        return $this->uri;
    }

    /**
     * Return an instance
     * with the specified request URI.
     *
     * @param  \Psr\Http\Message\UriInterface  $uri
     * @param  bool  $preserveHost
     *
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $new = clone $this;
        $new->uri = $uri;

        if ($preserveHost && $new->hasHeader('Host')) {
            return $new;
        }

        $new->setHostHeader();

        return $new;
    }

    /**
     * Get the string
     * representation of the request.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return to_string($this);
        } catch (Throwable $e) {}
    }

    /**
     * Set the Host header to the request.
     *
     * @return void
     */
    protected function setHostHeader()
    {
        $host = $this->uri->getHost();
        if ('' !== $host) {
            $port = $this->uri->getPort();
            if (null !== $port) {
                $host .= ':'.$port;
            }

            $this->setHeader('Host', $host);
        }
    }

    /**
     * Filter a request method.
     *
     * @param  string  $method
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterMethod($method)
    {
        if (! preg_match('/^[!#$%&\'*+\-.^_`|~0-9a-zA-Z]+$/', $method)) {
            throw new InvalidArgumentException('Invalid method! Method must be compliant with the "RFC 7230" standart.');
        }

        return $method;
    }
}
