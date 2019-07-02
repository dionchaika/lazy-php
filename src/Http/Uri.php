<?php

namespace Lazy\Http;

use Throwable;
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
        if ($uri) {
            $uriParts = parse_url($uri);

            if (false === $uriParts) {
                throw new InvalidArgumentException("Unable to parse the URI: {$uri}!");
            }

            $this->applyParts($uriParts);
        }
    }

    /**
     * Create a new URI from parts.
     *
     * Allowed URI parts:
     *      1. scheme (string) - the URI scheme.
     *      2. host (string) - the URI host.
     *      3. port (int) - the URI port.
     *      4. user (string) - the URI user.
     *      5. pass (string) - the URI password.
     *      6. path (string) - the URI path.
     *      7. query (string) - the URI query.
     *      8. fragment (string) - the URI fragment.
     *
     * @param  mixed[]  $parts
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public static function fromParts(array $parts)
    {
        $uri = new static;

        $uri->applyParts($parts);

        return $uri;
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
        $secured = ! empty($_SERVER['HTTPS']) && 0 !== strcasecmp($_SERVER['HTTPS'], 'off');

        $scheme = $secured ? 'https' : 'http';

        if (! empty($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'];
        } else {
            $host = ! empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
        }

        if (! empty($_SERVER['SERVER_PORT'])) {
            $port = (int) $_SERVER['SERVER_PORT'];
        } else {
            $port = $secured ? 443 : 80;
        }

        $path = ! empty($_SERVER['REQUEST_URI']) ? explode('?', $_SERVER['REQUEST_URI'], 2)[0] : '/';
        $query = ! empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

        return (new static)
            ->withScheme($scheme)
            ->withHost($host)
            ->withPort($port)
            ->withPath($path)
            ->withQuery($query);
    }

    /**
     * Check is the URI port is non-standard for the given URI scheme.
     *
     * @param  string  $scheme
     * @param  int|null  $port
     * @return bool
     */
    public static function isNonStandartPort($scheme, $port)
    {
        return ! isset(static::DEFAULT_PORTS[$scheme]) || $port !== static::DEFAULT_PORTS[$scheme];
    }

     /**
     * Get the URI scheme.
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Get the URI authority.
     *
     * @return string
     */
    public function getAuthority()
    {
        $authority = $this->host;

        if ($authority) {
            if ($this->userInfo) {
                $authority = $this->userInfo.'@'.$authority;
            }

            if ($this->port) {
                $authority .= ':'.$this->port;
            }
        }

        return $authority;
    }

    /**
     * Get the URI user information.
     *
     * @return string
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * Get the URI host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get the URI port.
     *
     * @return int|null
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get the URI path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get the URI query.
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get the array of URI query parameters.
     *
     * @return mixed[]
     */
    public function getQueryParams()
    {
        parse_str($this->query, $queryParams);

        return $queryParams;
    }

    /**
     * Check is the URI query parameter exists.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasQueryParam($name)
    {
        return isset($this->getQueryParams()[$name]);
    }

    /**
     * Get the URI query parameter.
     *
     * @param  string  $name
     * @return string
     */
    public function getQueryParam($name)
    {
        $queryParams = $this->getQueryParams();

        return isset($queryParams[$name]) ? $queryParams[$name] : '';
    }

    /**
     * Get the URI fragment.
     *
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Return an instance
     * with the specified URI scheme.
     *
     * @param  string  $scheme
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withScheme($scheme)
    {
        $new = clone $this;

        $new->scheme = $new->filterScheme($scheme);
        $new->port = static::isNonStandartPort($new->scheme, $new->port) ? $new->port : null;

        return $new;
    }

    /**
     * Return an instance
     * with the specified URI user information.
     *
     * @param  string  $user
     * @param  string|null  $password
     * @return static
     */
    public function withUserInfo($user, $password = null)
    {
        $userInfo = $user;

        if ($userInfo && $password) {
            $userInfo .= ':'.$password;
        }

        $new = clone $this;

        $new->userInfo = $userInfo;

        return $new;
    }

    /**
     * Return an instance
     * with the specified URI host.
     *
     * @param  string  $host
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withHost($host)
    {
        $new = clone $this;

        $new->host = $new->filterHost($host);

        return $new;
    }

    /**
     * Return an instance
     * with the specified URI port.
     *
     * @param  int|null  $port
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withPort($port)
    {
        $new = clone $this;

        $new->port = $new->filterPort($port);

        return $new;
    }

    /**
     * Return an instance
     * with the specified URI path.
     *
     * @param  string  $path
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withPath($path)
    {
        $new = clone $this;

        $new->path = $new->filterPath($path);

        return $new;
    }

    /**
     * Return an instance
     * with the specified URI query.
     *
     * @param  string  $query
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withQuery($query)
    {
        $new = clone $this;

        $new->query = $new->filterQuery($query);

        return $new;
    }

    /**
     * Return an instance
     * with the specified URI query parameter.
     *
     * @param  string  $name
     * @param  string  $value
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withQueryParam($name, $value)
    {
        $new = clone $this;

        $queryParams = explode('&', $new->query);

        $queryParams[] = $name.'='.$value;

        $new->query = $new->filterQuery(implode('&', $queryParams));

        return $new;
    }

    /**
     * Return an instance
     * with the specified URI query parameters.
     *
     * @param  mixed[]  $params
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public function withQueryParams($params)
    {
        $new = clone $this;

        foreach ($params as $name => $value) {
            $new = $new->withQueryParam($name, $value);
        }

        return $new;
    }

    /**
     * Return an instance
     * with the specified URI fragment.
     *
     * @param  string  $fragment
     * @return static
     */
    public function withFragment($fragment)
    {
        $new = clone $this;

        $new->fragment = $new->filterFragment($fragment);

        return $new;
    }

    /**
     * Check is the URI absolute.
     *
     * @return bool
     */
    public function isAbsolute()
    {
        return (bool) $this->scheme;
    }

    /**
     * Stringify the URI.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $uri = '';

            if ($this->scheme) {
                $uri .= $this->scheme.':';
            }

            $authority = $this->getAuthority();

            if ($authority) {
                $uri .= '//'.$authority;
            }

            if ($authority && 0 !== strpos($this->path, '/')) {
                $uri .= '/'.$this->path;
            } else if (! $authority && 0 === strpos($this->path, '//')) {
                $uri .= '/'.ltrim($this->path, '/');
            } else {
                $uri .= $this->path;
            }

            if ($this->query) {
                $uri .= '?'.$this->query;
            }

            if ($this->fragment) {
                $uri .= '#'.$this->fragment;
            }

            return $uri;
        } catch (Throwable $e) {
            trigger_error($e->getMessage(), \E_USER_ERROR);
        }
    }

    /**
     * Apply parts to the URI.
     *
     * @param  mixed[]  $parts
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function applyParts(array $parts)
    {
        $scheme = ! empty($parts['scheme']) ? $parts['scheme'] : '';
        $user = ! empty($parts['user']) ? $parts['user'] : '';
        $password = ! empty($parts['pass']) ? $parts['pass'] : null;
        $host = ! empty($parts['host']) ? $parts['host'] : '';
        $port = ! empty($parts['port']) ? $parts['port'] : null;
        $path = ! empty($parts['path']) ? $parts['path'] : '';
        $query = ! empty($parts['query']) ? $parts['query'] : '';
        $fragment = ! empty($parts['fragment']) ? $parts['fragment'] : '';

        $userInfo = $user;

        if ($userInfo && $password) {
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

    /**
     * Filter a URI scheme.
     *
     * @param  string  $scheme
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterScheme($scheme)
    {
        if ($scheme) {
            if (! preg_match('/^[a-zA-Z][a-zA-Z0-9+\-.]*$/', $scheme)) {
                throw new InvalidArgumentException('Invalid scheme! Scheme must be compliant with the "RFC 3986" standart.');
            }

            return strtolower($scheme);
        }

        return $scheme;
    }

    /**
     * Filter a URI host.
     *
     * @param  string  $host
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterHost($host)
    {
        if ($host) {
            //
            // Matching an IPvFuture or an IPv6address.
            //
            if (preg_match('/^\[.+\]$/', $host)) {
                $host = trim($host, '[]');

                //
                // Matching an IPvFuture.
                //
                if (preg_match('/^(v|V)/', $host)) {
                    if (! preg_match('/^(v|V)[a-fA-F0-9]\.([a-zA-Z0-9\-._~]|[!$&\'()*+,;=]|\:)$/', $host)) {
                        throw new InvalidArgumentException('Invalid host! IP address must be compliant with the "IPvFuture" of the "RFC 3986" standart.');
                    }
                //
                // Matching an IPv6address.
                //
                } else if (false === filter_var($host, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
                    throw new InvalidArgumentException('Invalid host! IP address must be compliant with the "IPv6address" of the "RFC 3986" standart.');
                }

                $host = '['.$host.']';
            //
            // Matching an IPv4address.
            //
            } else if (preg_match('/^([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\./', $host)) {
                if (false === filter_var($host, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
                    throw new InvalidArgumentException('Invalid host! IP address must be compliant with the "IPv4address" of the "RFC 3986" standart.');
                }
            //
            // Matching a domain name.
            //
            } else {
                if (! preg_match('/^([a-zA-Z0-9\-._~]|%[a-fA-F0-9]{2}|[!$&\'()*+,;=])*$/', $host)) {
                    throw new InvalidArgumentException('Invalid host! Host must be compliant with the "RFC 3986" standart.');
                }
            }

            return strtolower($host);
        }

        return $host;
    }

    /**
     * Filter a URI port.
     *
     * @param  int|null  $port
     * @return int|null
     *
     * @throws \InvalidArgumentException
     */
    protected function filterPort($port)
    {
        if (null !== $port) {
            if (1 > $port || 65535 < $port) {
                throw new InvalidArgumentException('Invalid port! TCP or UDP port must be between 1 and 65535.');
            }

            return static::isNonStandartPort($this->scheme, $port) ? $port : null;
        }

        return $port;
    }

    /**
     * Filter a URI path.
     *
     * @param  string  $path
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterPath($path)
    {
        if (! $this->scheme && 0 === strpos($path, ':')) {
            throw new InvalidArgumentException('Invalid path! Path of a URI without a scheme cannot begin with a colon.');
        }

        $authority = $this->getAuthority();

        if (! $authority && 0 === strpos($path, '//')) {
            throw new InvalidArgumentException('Invalid path! Path of a URI without an authority cannot begin with two slashes.');
        }

        if ($authority && $path && 0 !== strpos($path, '/')) {
            throw new InvalidArgumentException('Invalid path! Path of a URI with an authority must be empty or begin with a slash.');
        }

        if ($path && '/' !== $path) {
            if (! preg_match('/^([a-zA-Z0-9\-._~]|%[a-fA-F0-9]{2}|[!$&\'()*+,;=]|\:|\@|\/|\%)*$/', $path)) {
                throw new InvalidArgumentException('Invalid path! Path must be compliant with the "RFC 3986" standart.');
            }

            return preg_replace_callback('/(?:[^a-zA-Z0-9\-._~!$&\'()*+,;=:@\/%]++|%(?![a-fA-F0-9]{2}))/', function ($matches) {
                return rawurlencode($matches[0]);
            }, $path);
        }

        return $path;
    }

    /**
     * Filter a URI query.
     *
     * @param  string  $query
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function filterQuery($query)
    {
        if ($query) {
            if (! preg_match('/^([a-zA-Z0-9\-._~]|%[a-fA-F0-9]{2}|[!$&\'()*+,;=]|\:|\@|\/|\?|\%)*$/', $query)) {
                throw new InvalidArgumentException('Invalid query! Query must be compliant with the "RFC 3986" standart.');
            }

            return preg_replace_callback('/(?:[^a-zA-Z0-9\-._~!$&\'()*+,;=:@\/?%]++|%(?![a-fA-F0-9]{2}))/', function ($matches) {
                return rawurlencode($matches[0]);
            }, $query);
        }

        return $query;
    }

    /**
     * Filter a URI fragment.
     *
     * @param  string  $fragment
     * @return string
     */
    protected function filterFragment($fragment)
    {
        if ($fragment) {
            return preg_replace_callback('/(?:[^a-zA-Z0-9\-._~!$&\'()*+,;=:@\/?%]++|%(?![a-fA-F0-9]{2}))/', function ($matches) {
                return rawurlencode($matches[0]);
            }, $fragment);
        }

        return $fragment;
    }
}
