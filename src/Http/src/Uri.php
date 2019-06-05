<?php

namespace Lazy\Http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * The PSR-7 URI implementation class.
 *
 * @see https://www.php-fig.org/psr/psr-7/
 * @see https://www.ietf.org/rfc/rfc3986.txt
 */
class Uri implements UriInterface
{
    /**
     * The default ports.
     */
    const DEFAULT_PORTS = [

        'http'  => 80,
        'https' => 443

    ];

    /**
     * The URI scheme.
     *
     * @var string
     */
    protected $scheme = '';

    /**
     * The URI user information.
     *
     * @var string
     */
    protected $userInfo = '';

    /**
     * The URI host.
     *
     * @var string
     */
    protected $host = '';

    /**
     * The URI port.
     *
     * @var int|null
     */
    protected $port;

    /**
     * The URI path.
     *
     * @var string
     */
    protected $path = '';

    /**
     * The URI query.
     *
     * @var string
     */
    protected $query = '';

    /**
     * The URI fragment.
     *
     * @var string
     */
    protected $fragment = '';

    /**
     * The URI constructor.
     *
     * @param  string  $uri
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($uri = '')
    {
        if ('' !== $uri) {
            $uriParts = parse_url($uri);
            if (false === $uriParts) {
                throw new InvalidArgumentException('Unable to parse the URI: '.$uri.'!');
            }

            $scheme = !empty($uriParts['scheme']) ? $uriParts['scheme'] : '';
            $user = !empty($uriParts['user']) ? $uriParts['user'] : '';
            $password = !empty($uriParts['pass']) ? $uriParts['pass'] : null;
            $host = !empty($uriParts['host']) ? $uriParts['host'] : '';
            $port = !empty($uriParts['port']) ? $uriParts['port'] : null;
            $path = !empty($uriParts['path']) ? $uriParts['path'] : '';
            $query = !empty($uriParts['query']) ? $uriParts['query'] : '';
            $fragment = !empty($uriParts['fragment']) ? $uriParts['fragment'] : '';

            $userInfo = $user;
            if ('' !== $userInfo && null !== $password && '' !== $password) {
                $userInfo .= ':'.$password;
            }

            $this->scheme = $this->filterScheme($scheme);
            $this->userInfo = $userInfo;
            $this->host = $this->filterHost($host);
            $this->port = $this->filterPort($port);
            $this->path = $this->filterPath($path);
            $this->query = $this->filterQuery($query);
            $this->fragment = $this->filterFragment($fragment);
        }
    }

    /**
     * Create a new URI from globals.
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public static function fromGlobals()
    {
        $secured = !empty($_SERVER['HTTPS']) && 0 !== strcasecmp($_SERVER['HTTPS'], 'off');

        $scheme = $secured ? 'https' : 'http';
        $host = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : (!empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1');
        $port = !empty($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : ($secured ? 443 : 80);
        $path = !empty($_SERVER['REQUEST_URI']) ? explode('?', $_SERVER['REQUEST_URI'], 2)[0] : '/';
        $query = !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

        return (new static)
            ->withScheme($scheme)
            ->withHost($host)
            ->withPort($port)
            ->withPath($path)
            ->withQuery($query);
    }
}
