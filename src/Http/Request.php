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
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
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
            $this->setHostHeaderFromUri($this->uri);
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
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public static function fromString($request)
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
        if ($this->requestTarget) {
            return $this->requestTarget;
        }

        if (! $this->uri) {
            return '/';
        }

        $requestTarget = '/'.ltrim($this->uri->getPath(), '/');

        $query = $this->uri->getQuery();

        if ($query) {
            $requestTarget = "{$requestTarget}?{$query}";
        }

        return $requestTarget;
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

        $new->setHostHeaderFromUri($new->uri);

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
     * Check is the request an AJAX request.
     *
     * An alias method name to isXhr.
     *
     * @return bool
     */
    public function isAjax()
    {
        return $this->isXhr();
    }

    /**
     * Check is the request an XHR request.
     *
     * @return bool
     */
    public function isXhr()
    {
        return 0 === strcasecmp($this->getHeaderLine('X-Requested-With'), 'XMLHttpRequest');
    }

    /**
     * Check is the request an HTTPS request.
     *
     * An alias method name to isSecured.
     *
     * @return bool
     */
    public function isHttps()
    {
        return $this->isSecured();
    }

    /**
     * Check is the request secured.
     *
     * @return bool
     */
    public function isSecured()
    {
        return 'https' === $this->uri->getScheme();
    }

    /**
     * Check is the request a GET request.
     *
     * @return bool
     */
    public function isGet()
    {
        return $this->method === Method::GET;
    }

    /**
     * Check is the request a PUT request.
     *
     * @return bool
     */
    public function isPut()
    {
        return $this->method === Method::PUT;
    }

    /**
     * Check is the request a HEAD request.
     *
     * @return bool
     */
    public function isHead()
    {
        return $this->method === Method::HEAD;
    }

    /**
     * Check is the request a POST request.
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->method === Method::POST;
    }

    /**
     * Check is the request a PATCH request.
     *
     * @return bool
     */
    public function isPatch()
    {
        return $this->method === Method::PATCH;
    }

    /**
     * Check is the request a TRACE request.
     *
     * @return bool
     */
    public function isTrace()
    {
        return $this->method === Method::TRACE;
    }

    /**
     * Check is the request a DELETE request.
     *
     * @return bool
     */
    public function isDelete()
    {
        return $this->method === Method::DELETE;
    }

    /**
     * Check is the request an OPTIONS request.
     *
     * @return bool
     */
    public function isOptions()
    {
        return $this->method === Method::OPTIONS;
    }

    /**
     * Check is the request a CONNECT request.
     *
     * @return bool
     */
    public function isConnect()
    {
        return $this->method === Method::CONNECT;
    }

    /**
     * Stringify the request.
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
     * Set the request Host header from the URI.
     *
     * @param  \Psr\Http\Message\UriInterface  $uri
     * @return void
     */
    protected function setHostHeaderFromUri(UriInterface $uri)
    {
        $host = $uri->getHost();

        if ($host) {
            $port = $this->uri->getPort();

            if ($port) {
                $host = "{$host}:{$port}";
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
