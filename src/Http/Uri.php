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
     * The default environments
     */
    const DEFAULT_ENVIRONMENTS = [

        'HTTPS'         => 'off',
        'PHP_AUTH_USER' => '',
        'PHP_AUTH_PW'   => '',
        'SERVER_NAME'   => 'localhost',
        'SERVER_PORT'   => '80',
        'REQUEST_URI'   => '/',
        'QUERY_STRING'  => ''

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
     * @param  string  $scheme
     * @param  string  $user
     * @param  string|null  $password
     * @param  string  $host
     * @param  int|null  $port
     * @param  string  $path
     * @param  string  $query
     * @param  string  $fragment
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($scheme = '',
                                $user = '',
                                $password = null,
                                $host = '',
                                $port = null,
                                $path = '',
                                $query = '',
                                $fragment = '')
    {
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
     * Create a new URI from PHP globals.
     *
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function fromGlobals()
    {
        return static::fromEnvironments($_SERVER);
    }

    /**
     * Create a new URI from environments.
     *
     * @param  array  $environments
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function fromEnvironments(array $environments)
    {
        $environments = array_merge(static::DEFAULT_ENVIRONMENTS, $environments);

        $scheme = 'off' === $environments['HTTPS'] ? 'http' : 'https';
        $user = $environments['PHP_AUTH_USER'];
        $password = $environments['PHP_AUTH_PW'] ? $environments['PHP_AUTH_PW'] : null;
        $host = $environments['SERVER_NAME'];
        $port = (int) $environments['SERVER_PORT'];
        $path = explode('?', $environments['REQUEST_URI'], 2)[0];
        $query = $environments['QUERY_STRING'];

        return new static($scheme, $user, $password, $host, $port, $path, $query);
    }

    /**
     * Create a new URI from string.
     *
     * @param  string  $uri
     * @return static
     *
     * @throws \InvalidArgumentException
     */
    public static function fromString($uri)
    {
        $parts = parse_url($uri);

        if (false === $parts) {
            throw new InvalidArgumentException("Unable to parse the URI: {$uri}!");
        }

        $scheme = ! empty($parts['scheme']) ? $parts['scheme'] : '';
        $user = ! empty($parts['user']) ? $parts['user'] : '';
        $password = ! empty($parts['pass']) ? $parts['pass'] : null;
        $host = ! empty($parts['host']) ? $parts['host'] : '';
        $port = ! empty($parts['port']) ? $parts['port'] : null;
        $path = ! empty($parts['path']) ? $parts['path'] : '';
        $query = ! empty($parts['query']) ? $parts['query'] : '';
        $fragment = ! empty($parts['fragment']) ? $parts['fragment'] : '';

        return new static($scheme, $user, $password, $host, $port, $path, $query, $fragment);
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

            if (null !== $this->port) {
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
     * @return array
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
        $queryParams = explode('&', $this->query);

        $queryParams[] = $name.'='.$value;

        return $this->withQuery(implode('&', $queryParams));
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
    public function withQueryParams(array $params)
    {
        $queryParams = explode('&', $this->query);

        foreach ($params as $name => $value) {
            $queryParams[] = $name.'='.$value;
        }

        return $this->withQuery(implode('&', $queryParams));
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
                throw new InvalidArgumentException(
                    "Invalid scheme: {$scheme}! "
                    ."Scheme must be compliant with the \"RFC 3986\" standart."
                );
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
                        throw new InvalidArgumentException(
                            "Invalid host: {$host}! "
                            ."IP address must be compliant with the \"IPvFuture\" of the \"RFC 3986\" standart."
                        );
                    }
                //
                // Matching an IPv6address.
                //
                } else if (false === filter_var($host, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
                    throw new InvalidArgumentException(
                        "Invalid host: {$host}! "
                        ."IP address must be compliant with the \"IPv6address\" of the \"RFC 3986\" standart."
                    );
                }

                $host = '['.$host.']';
            //
            // Matching an IPv4address.
            //
            } else if (preg_match('/^([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\./', $host)) {
                if (false === filter_var($host, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
                    throw new InvalidArgumentException(
                        "Invalid host: {$host}! "
                        ."IP address must be compliant with the \"IPv4address\" of the \"RFC 3986\" standart."
                    );
                }
            //
            // Matching a domain name.
            //
            } else {
                if (! preg_match('/^([a-zA-Z0-9\-._~]|%[a-fA-F0-9]{2}|[!$&\'()*+,;=])*$/', $host)) {
                    throw new InvalidArgumentException(
                        "Invalid host: {$host}! "
                        ."Host must be compliant with the \"RFC 3986\" standart."
                    );
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
                throw new InvalidArgumentException(
                    "Invalid port: {$port}! "
                    ."TCP or UDP port must be between 1 and 65535."
                );
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
            throw new InvalidArgumentException(
                "Invalid path: {$path}! "
                ."Path of a URI without a scheme cannot begin with a colon."
            );
        }

        $authority = $this->getAuthority();

        if (! $authority && 0 === strpos($path, '//')) {
            throw new InvalidArgumentException(
                "Invalid path: {$path}! "
                ."Path of a URI without an authority cannot begin with two slashes."
            );
        }

        if ($authority && $path && 0 !== strpos($path, '/')) {
            throw new InvalidArgumentException(
                "Invalid path: {$path}! "
                ."Path of a URI with an authority must be empty or begin with a slash."
            );
        }

        if ($path && '/' !== $path) {
            if (! preg_match('/^([a-zA-Z0-9\-._~]|%[a-fA-F0-9]{2}|[!$&\'()*+,;=]|\:|\@|\/|\%)*$/', $path)) {
                throw new InvalidArgumentException(
                    "Invalid path: {$path}! "
                    ."Path must be compliant with the \"RFC 3986\" standart."
                );
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
                throw new InvalidArgumentException(
                    "Invalid query: {$query}! "
                    ."Query must be compliant with the \"RFC 3986\" standart."
                );
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
