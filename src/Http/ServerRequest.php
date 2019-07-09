<?php

namespace Lazy\Http;

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
    use BodyParserTrait;

    /**
     * The request parsed body.
     *
     * @var array|object|null
     */
    protected $parsedBody;

    /**
     * The array of request attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The array of request query parameters.
     *
     * @var array
     */
    protected $queryParams = [];

    /**
     * The array of request server parameters.
     *
     * @var array
     */
    protected $serverParams = [];

    /**
     * The array of request cookie parameters.
     *
     * @var array
     */
    protected $cookieParams = [];

    /**
     * The array of request uploaded files.
     *
     * @var array
     */
    protected $uploadedFiles = [];

    /**
     * The request constructor.
     *
     * @param  string  $method
     * @param  \Psr\Http\Message\UriInterface|string|null  $uri
     * @param  \Lazy\Http\Headers|array|null  $headers
     * @param  mixed|null  $body
     * @param  array  $serverParams
     * @param  string  $protocolVersion
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($method = Method::GET,
                                $uri = null,
                                $headers = null,
                                $body = null,
                                array $serverParams = [],
                                $protocolVersion = '1.1')
    {
        $this->serverParams = $serverParams;

        parent::__construct($method, $uri, $headers, $body, $protocolVersion);

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
        return static::fromEnvironment($_SERVER);
    }

    /**
     * Create a new request from environment.
     *
     * @param  array  $environment
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function fromEnvironment(array $environment)
    {
        $method = ! empty($environment['REQUEST_METHOD']) ? $environment['REQUEST_METHOD'] : 'GET';

        $protocolVersion = '1.1';

        if (
            ! empty($environment['SERVER_PROTOCOL']) &&
            preg_match('/^HTTP\/(\d\.\d)$/', $environment['SERVER_PROTOCOL'], $matches)
        ) {
            $protocolVersion = $matches[1];
        }

        $uri = Uri::fromGlobals();
        $headers = Headers::fromGlobals();
        $uploadedFiles = UploadedFile::fromGlobals();

        $request = (new static($method, $uri, $headers, fopen('php://input', 'r'), $environment))
            ->withQueryParams($_GET)
            ->withCookieParams($_COOKIE)
            ->withUploadedFiles($uploadedFiles)
            ->withProtocolVersion($protocolVersion);

        $contentMimeType = $this->getContentMimeType();

        if (
            'POST' === $method &&
            in_array($contentMimeType, ['multipart/form-data', 'application/x-www-form-urlencoded'])
        ) {
            $request = $request->withParsedBody($_POST);
        } else {
            foreach ($this->parsers as $mimeType => $callable) {
                if ($mimeType === $contentMimeType) {
                    return call_user_func($callable, $this);
                }
            }
        }

        return $request;
    }

    /**
     * Get the array of request server parameters.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Get the array of request cookie parameters.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * Return an instance with
     * the specified request cookie parameters.
     *
     * @param  array  $cookies
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
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * Return an instance with
     * the specified request query parameters.
     *
     * @param  array  $query
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
     * @return array
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * Return an instance
     * with the specified request uploaded files.
     *
     * @param  array  $uploadedFiles
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
     * @return array|object|null
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Return an instance
     * with the specified request parsed body.
     *
     * @param  array|object|null  $data
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
     * @return array
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
     * Filter an array of request uploaded files.
     *
     * @param  array  $uploadedFiles
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
     * @param  array|object|null  $data
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
