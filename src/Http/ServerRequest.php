<?php

namespace Lazy\Http;

use BadMethodCallException;
use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The PSR-7 HTTP request message
 * implementation class for server handling.
 *
 * @see https://www.php-fig.org/psr/psr-7/
 * @see https://tools.ietf.org/html/rfc7230
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * The request parsed body.
     *
     * @var mixed[]|object|null
     */
    protected $parsedBody;

    /**
     * The array of request attributes.
     *
     * @var mixed[]
     */
    protected $attributes = [];

    /**
     * The array of request query parameters.
     *
     * @var mixed[]
     */
    protected $queryParams = [];

    /**
     * The array of request server parameters.
     *
     * @var mixed[]
     */
    protected $serverParams = [];

    /**
     * The array of request cookie parameters.
     *
     * @var mixed[]
     */
    protected $cookieParams = [];

    /**
     * The array of request uploaded files.
     *
     * @var mixed[]
     */
    protected $uploadedFiles = [];

    /**
     * The request constructor.
     *
     * @param  string  $method
     * @param  \Psr\Http\Message\UriInterface|string|null  $uri
     * @param  mixed[]  $serverParams
     * @param  mixed[]  $headers
     * @param  \Psr\Http\Message\StreamInterface|string|resource|null  $body
     * @param  string  $protocolVersion
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct($method = Method::GET,
                                $uri = null,
                                $serverParams = [],
                                $headers = [],
                                $body = null,
                                $protocolVersion = '1.1')
    {
        $this->serverParams = $serverParams;

        parent::__construct($method, $uri, $headers, $body, $protocolVersion);
    }

    /**
     * {@inheritDoc}
     */
    public static function fromString($request)
    {
        throw new BadMethodCallException('Method "fromString" is unavaliable for server requests!');
    }

    /**
     * Create a new request from globals.
     *
     * @return self
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public static function fromGlobals()
    {
        if (isset($_POST['_method'])) {
            $method = $_POST['_method'];
        } else {
            $method = $this->getOriginalMethod();
        }

        $protocolVersion = '1.1';

        if (
            ! empty($_SERVER['SERVER_PROTOCOL'])
            && preg_match('/^HTTP\/(\d\.\d)$/', $_SERVER['SERVER_PROTOCOL'], $matches)
        ) {
            $protocolVersion = $matches[1];
        }

        $uri = Uri::fromGlobals();
        $uploadedFiles = UploadedFile::fromGlobals();

        $request = (new static($method, $uri, $_SERVER))
            ->withProtocolVersion($protocolVersion)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withCookieParams($_COOKIE)
            ->withUploadedFiles($uploadedFiles);

        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headerName = strtolower(str_replace('_', '-', substr($key, 5)));
                $headerName = implode('-', array_map('ucfirst', explode('-', $headerName)));

                if (0 === strcasecmp($headerName, 'cookie')) {
                    $headerValue = array_map('trim', explode(';', $value));
                } else {
                    $headerValue = array_map('trim', explode(',', $value));
                }

                $request = $request->withHeader($headerName, $headerValue);
            }
        }

        if ($request->hasHeader('X-HTTP-Method')) {
            $request = $request->withMethod($request->getHeaderLine('X-HTTP-Method'));
        } else if ($request->hasHeader('X-HTTP-Method-Override')) {
            $request = $request->withMethod($request->getHeaderLine('X-HTTP-Method-Override'));
        }

        if ('1.1' === $protocolVersion && ! $request->hasHeader('Host')) {
            throw new InvalidArgumentException('Invalid request! "HTTP/1.1" request must contain a "Host" header.');
        }

        return $request->withBody(new Stream('php://input'));
    }

    /**
     * Get the array of request server parameters.
     *
     * @return mixed[]
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Get the array of request cookie parameters.
     *
     * @return mixed[]
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * Return an instance with
     * the specified request cookie parameters.
     *
     * @param  mixed[]  $cookies
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $new = clone $this;

        $new->cookieParams = $cookies;

        return $new;
    }

    /**
     * Get the array of request query parameters.
     *
     * @return mixed[]
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * Return an instance with
     * the specified request query parameters.
     *
     * @param  mixed[]  $query
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $new = clone $this;

        $new->queryParams = $query;

        return $new;
    }

    /**
     * Get the array of request uploaded files.
     *
     * @return mixed[]
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * Return an instance
     * with the specified request uploaded files.
     *
     * @param  mixed[]  $uploadedFiles
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $new = clone $this;

        $new->uploadedFiles = $new->filterUploadedFiles($uploadedFiles);

        return $new;
    }

    /**
     * Get the request parsed body.
     *
     * @return mixed[]|object|null
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Return an instance
     * with the specified request parsed body.
     *
     * @param  mixed[]|object|null  $data
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withParsedBody($data)
    {
        $new = clone $this;

        $new->parsedBody = $new->filterParsedBody($data);

        return $new;
    }

    /**
     * Get the array of request attributes.
     *
     * @return mixed[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get the request attribute.
     *
     * @param  string  $name
     * @param  mixed|null  $default
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * Return an instance
     * with the specified request attribute.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $new = clone $this;

        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * Return an instance
     * without the specified request attribute.
     *
     * @param  string  $name
     * @return static
     */
    public function withoutAttribute($name)
    {
        $new = clone $this;

        unset($new->attributes[$name]);

        return $new;
    }

    /**
     * Check is the request method overriden.
     *
     * @return bool
     */
    public function isMethodOverriden()
    {
        return $this->method !== $this->getOriginalMethod();
    }

    /**
     * Get the request original method.
     *
     * @return string
     */
    public function getOriginalMethod()
    {
        return ! empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
    }

    /**
     * Filter an array of request uploaded files.
     *
     * @param  mixed[]  $uploadedFiles
     * @return mixed[]
     *
     * @throws \InvalidArgumentException
     */
    protected function filterUploadedFiles(array $uploadedFiles)
    {
        foreach ($uploadedFiles as $uploadedFile) {
            if (is_array($uploadedFile)) {
                $this->filterUploadedFiles($uploadedFile);
            } else if (! $uploadedFile instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('Invalid structure of uploaded files tree!');
            }
        }

        return $uploadedFiles;
    }

    /**
     * Filter a request parsed body.
     *
     * @param  mixed[]|object|null  $data
     * @return mixed[]|object|null
     *
     * @throws \InvalidArgumentException
     */
    protected function filterParsedBody($data)
    {
        if (null !== $data && ! is_array($data) && ! is_object($data)) {
            throw new InvalidArgumentException('Invalid parsed body! Parsed body must be an array or an object.');
        }

        return $data;
    }
}
