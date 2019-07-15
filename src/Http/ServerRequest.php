<?php

namespace Lazy\Http;

use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * {@inheritDoc}
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    use BodyParserTrait;

    /**
     * The default media types.
     */
    const DEFAULT_MEDIA_TYPES =  ['multipart/form-data', 'application/x-www-form-urlencoded'];

    /**
     * The default environments.
     */
    const DEFAULT_ENVIRONMENTS = [

        'REQUEST_METHOD'  => 'GET',
        'SERVER_PROTOCOL' => 'HTTP/1.1'

    ];

    /**
     * The request parsed body.
     *
     * Note: Contains FALSE
     * if the request body is not parsed yet.
     *
     * @var array|object|null
     */
    protected $parsedBody = false;

    /**
     * @var array The array of request attributes.
     */
    protected $attributes = [];

    /**
     * @var array The array of request query parameters.
     */
    protected $queryParams = [];

    /**
     * @var array The array of request server parameters.
     */
    protected $serverParams = [];

    /**
     * @var array The array of request cookie parameters.
     */
    protected $cookieParams = [];

    /**
     * @var array The array of request uploaded files.
     */
    protected $uploadedFiles = [];

    /**
     * @var string The original request method.
     */
    protected $originalMethod = Method::GET;

    /**
     * The request constructor.
     *
     * @param  string  $method  The request method.
     * @param  \Psr\Http\Message\UriInterface|string|null  $uri  The request URI.
     * @param  \Lazy\Http\Headers|array|null  $headers  The request headers.
     * @param  \Psr\Http\Message\StreamInterface|mixed|null  $body  The request body.
     * @param  array  $serverParams  The array of request server parameters.
     * @param  string  $protocolVersion  The request protocol version.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($method = Method::GET, $uri = '/', $headers = [], $body = null, array $serverParams = [], $protocolVersion = '1.1')
    {
        $this->serverParams = $serverParams;

        parent::__construct($method, $uri, $headers, $body, $protocolVersion);

        $this->originalMethod = $method;

        if ($this->hasHeader(Header::X_HTTP_METHOD_OVERRIDE)) {
            $this->method = $this->filterMethod($this->getHeaderLine(Header::X_HTTP_METHOD_OVERRIDE));
        }

        $this->registerParser('text/xml', $this->getDefaultXmlParser());
        $this->registerParser('application/json', $this->getDefaultJsonParser());
        $this->registerParser('application/x-www-form-urlencoded', $this->getDefaultUrlencodedParser());
    }

    /**
     * Create a new request from PHP globals.
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function fromGlobals()
    {
        $request = (static::fromEnvironments($_SERVER))
            ->withQueryParams($_GET)
            ->withCookieParams($_COOKIE)
            ->withUploadedFiles(UploadedFile::fromGlobals());

        if (
            'POST' === $request->getOriginalMethod() &&
            in_array($request->getMediaType(), static::DEFAULT_MEDIA_TYPES)
        ) {
            $request = $request->withParsedBody($_POST);
        }

        return $request;
    }

    /**
     * Create a new request from environments.
     *
     * @param  array  $environments  The array of environments.
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function fromEnvironments(array $environments)
    {
        $environments = array_merge(static::DEFAULT_ENVIRONMENTS, $environments);

        $method = $environments['REQUEST_METHOD'];
        $protocolVersion = explode('/', $environments['SERVER_PROTOCOL'], 2)[1];

        $uri = Uri::fromEnvironments($environments);
        $headers = Headers::fromEnvironments($environments);

        return new static($method, $uri, $headers, fopen('php://input', 'r'), $environments, $protocolVersion);
    }

    /**
     * {@inheritDoc}
     */
    public static function fromString($request)
    {
        trigger_error('Method "fromString" is not supported by the server requests!', \E_USER_ERROR);
    }

    /**
     * {@inheritDoc}
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * {@inheritDoc}
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * {@inheritDoc}
     */
    public function withCookieParams(array $cookies)
    {
        $new = clone $this;

        $new->cookieParams = $cookies;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * {@inheritDoc}
     */
    public function withQueryParams(array $query)
    {
        $new = clone $this;

        $new->queryParams = $query;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritDoc}
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $new = clone $this;

        $new->uploadedFiles = $new->filterUploadedFiles($uploadedFiles);

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getParsedBody()
    {
        if (false === $this->parsedBody) {
            $mediaType = $this->getMediaType();

            $this->parsedBody = $this->filterParsedBody(
                isset($this->parsers[$mediaType]) ? call_user_func($this->parsers[$mediaType], $this->body) : null
            );
        }

        return $this->parsedBody;
    }

    /**
     * {@inheritDoc}
     */
    public function withParsedBody($data)
    {
        $new = clone $this;

        $new->parsedBody = $new->filterParsedBody($data);

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function withAttribute($name, $value)
    {
        $new = clone $this;

        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutAttribute($name)
    {
        $new = clone $this;

        unset($new->attributes[$name]);

        return $new;
    }

    /**
     * Get the original request method.
     *
     * @return string
     */
    public function getOriginalMethod()
    {
        return $this->originalMethod;
    }

    /**
     * Check is the request method overridden.
     *
     * @return bool
     */
    public function isMethodOverridden()
    {
        return $this->method !== $this->originalMethod;
    }

    /**
     * Filter an array of request uploaded files.
     *
     * @param  array  $uploadedFiles  The array of request uploaded files.
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function filterUploadedFiles(array $uploadedFiles)
    {
        foreach ($uploadedFiles as $uploadedFile) {
            if (is_array($uploadedFile)) {
                $this->filterUploadedFiles($uploadedFile);
            } else if (! $uploadedFile instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('Invalid structure of the uploaded files tree!');
            }
        }

        return $uploadedFiles;
    }

    /**
     * Filter a request parsed body.
     *
     * @param  array|object|null  $data  The request parsed body.
     * @return array|object|null
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
