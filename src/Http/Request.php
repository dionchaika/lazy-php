<?php

namespace Lazy\Http;

use Throwable;
use Lazy\Cookie\Cookie;
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
     * The request URI.
     *
     * @var \Psr\Http\Message\UriInterface
     */
    protected $uri;

    /**
     * The request method.
     *
     * @var string
     */
    protected $method = Method::GET;

    /**
     * The request target.
     *
     * @var string
     */
    protected $requestTarget;

    /**
     * The request constructor.
     *
     * @param  string  $method
     * @param  \Psr\Http\Message\UriInterface|string|null  $uri
     * @param  mixed[]  $headers
     * @param  \Psr\Http\Message\StreamInterface|string|resource|null  $body
     * @param  string  $protocolVersion
     */
    public function __construct($method = Method::GET,
                                $uri = null,
                                $headers = [],
                                $body = null,
                                $protocolVersion = '1.1')
    {
        $this->method = $this->filterMethod($method);

        if (! $uri) {
            $this->uri = new Uri;
        } else if (is_string($uri)) {
            $this->uri = new Uri($uri);
        }

        $this->setHeaders($headers);

        if ('1.1' === $this->protocolVersion && ! $this->hasHeader('Host')) {
            $this->setHostHeader();
        }

        if (! $body) {
            $this->body = new Stream;
        } else if (is_string($body) || is_resource($body)) {
            $this->body = new Stream($body);
        }

        $this->protocolVersion = $protocolVersion;
    }

    /**
     * Create a new request from string.
     *
     * @param  string  $request
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $request): self
    {
        return parse_request($request);
    }

    /**
     * Get the request target.
     *
     * @return string
     */
    public function getRequestTarget()
    {
        if (! empty($this->requestTarget)) {
            return $this->requestTarget;
        }

        if ($this->uri) {
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
        if (! $this->uri) {
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
     * Return an instance
     * with the specified request cookie.
     *
     * @param  \Lazy\Cookie\Cookie  $cookie
     * @return static
     */
    public function withCookie(Cookie $cookie)
    {
        return $this->withAddedHeader('Cookie', $cookie->getNameValuePair());
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
        } catch (Throwable $e) {
            trigger_error($e->getMessage(), \E_USER_ERROR);
        }
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

            $host = [

                'host' => [

                    'name'   => 'Host',
                    'values' => [$host]

                ]

            ];

            $this->headers = array_merge($host, $this->headers);
        }
    }

    /**
     * Filter a request method.
     *
     * @param  string  $method
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
