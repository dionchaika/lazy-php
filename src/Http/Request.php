<?php

namespace Lazy\Http;

use Throwable;
use SimpleXMLElement;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;

/**
 * {@inheritDoc}
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
     * @param  \Lazy\Http\Headers|array|null  $headers
     * @param  \Psr\Http\Message\StreamInterface|mixed|null  $body
     * @param  string  $protocolVersion
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($method = Method::GET,
                                $uri = null,
                                $headers = null,
                                $body = null,
                                $protocolVersion = '1.1')
    {
        $this->method = $this->filterMethod($method);

        if (! $uri) {
            $this->uri = new Uri;
        } else {
            $this->uri = ($uri instanceof UriInterface)
                ? $uri
                : Uri::fromString($uri);
        }

        if (! $headers) {
            $this->headers = new Headers;
        } else {
            $this->headers = ($headers instanceof Headers)
                ? $headers
                : new Headers($headers);
        }

        if (! $this->hasHeader('Host')) {
            $this->setHostHeaderFromUri($this->uri);
        }

        $this->body = create_stream($body);
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * Create a new request from string.
     *
     * @param  string  $request
     * @return \Lazy\Http\Request
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public static function fromString($request)
    {
        return parse_request($request);
    }

    /**
     * {@inheritDoc}
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
            $requestTarget .= '?'.$query;
        }

        return $requestTarget;
    }

    /**
     * {@inheritDoc}
     */
    public function withRequestTarget($requestTarget)
    {
        $new = clone $this;

        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritDoc}
     */
    public function withMethod($method)
    {
        $new = clone $this;

        $new->method = $new->filterMethod($method);

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri()
    {
        if (! $this->uri) {
            $this->uri = new Uri();
        }

        return $this->uri;
    }

    /**
     * {@inheritDoc}
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
     * @param  \Lazy\Http\Cookie  $cookie
     * @return static
     */
    public function withCookie(Cookie $cookie)
    {
        return $this->withAddedHeader('Cookie', $cookie->getPair());
    }

    /**
     * Return an instance
     * with the plain text request body.
     *
     * @param  mixed  $plainText
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withPlainText($plainText)
    {
        $new = (clone $this)->withBody(create_stream($plainText));

        return $new
            ->withHeader('Content-Type', 'text/plain')
            ->withHeader('Content-Length', (string) $new->getBody()->getSize());
    }

    /**
     * Return an instance
     * with the JSON request body.
     *
     * @param  mixed  $data
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withJson($data, $opts = 0, $depth = 512)
    {
        $new = (clone $this)->withBody(
            create_stream(json_encode($data, $opts, $depth))
        );

        return $new
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', (string) $new->getBody()->getSize());
    }

    /**
     * Return an instance
     * with the XML request body.
     *
     * @param  \SimpleXMLElement  $xml
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withXml(SimpleXMLElement $xml)
    {
        $new = (clone $this)->withBody(
            create_stream($xml->asXML())
        );

        return $new
            ->withHeader('Content-Type', 'text/xml')
            ->withHeader('Content-Length', (string) $new->getBody()->getSize());
    }

    /**
     * Return an instance
     * with the application/x-www-form-urlencoded request body.
     *
     * @param  mixed  $data
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withUrlencoded($data)
    {
        $new = (clone $this)->withBody(
            create_stream(http_build_query($data))
        );

        return $new
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader('Content-Length', (string) $new->getBody()->getSize());
    }

    /**
     * Return an instance
     * with the multipart/form-data request body.
     *
     * @param  \Lazy\Http\FormData|array  $data
     * @param  string|null  $boundary
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withFormData($data, $boundary = null)
    {
        $data = ($data instanceof FormData)
            ? $data
            : new FormData($data, $boundary);

        $new = (clone $this)->withBody($data->getStream());

        return $new->withHeader('Content-Type', sprintf("multipart/form-data; boundary=%s", $data->getBoundary()));
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
            return stringify($this);
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

            if (null !== $port) {
                $host .= ':'.$port;
            }

            $host = [

                'host' => [

                    'name'   => 'Host',
                    'values' => [$host]

                ]

            ];

            $this->headers = new Headers(
                array_merge($host, $this->headers->all())
            );
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
            throw new InvalidArgumentException(
                "Invalid method: {$method}! "
                ."Method must be compliant with the \"RFC 7230\" standart."
            );
        }

        return $method;
    }
}
