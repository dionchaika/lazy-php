<?php

namespace Lazy\Http;

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
    protected $method = 'GET';

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
    public function __construct($method = 'GET', $uri = null, $headers = [], $protocolVersion = '1.1')
    {
        $this->method = $this->filterMethod($method);

        if (null === $uri) {
            $uri = new Uri;
        } else if (is_string($uri)) {
            $uri = new Uri($uri);
        }

        $this->uri = $uri;

        foreach ($headers as $name => $value) {
            $this->headers[strtolower($name)] = [

                'name'   => $this->filterHeaderName($name),
                'values' => $this->filterHeaderValue($value)

            ];
        }

        if ('1.1' === $this->protocolVersion && !$this->hasHeader('Host')) {
            $this->appendHostHeader();
        }

        $this->protocolVersion = $protocolVersion;
    }
}
