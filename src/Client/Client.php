<?php

namespace Lazy\Client;

use Throwable;
use Lazy\Http\Method;
use Lazy\Http\Request;
use Lazy\Cookie\CookieStorage;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The PSR-18 HTTP client implementation class.
 *
 * @see https://www.php-fig.org/psr/psr-18/
 */
class Client implements ClientInterface
{
    use ClientTrait;

    /**
     * The array of client config options.
     *
     * @var mixed[]
     */
    protected $config = [

        'headers'             => [],
        'cookies'             => true,
        'cookies_file'        => null,
        'proxy'               => [],
        'basic_auth'          => [],
        'origin_header'       => false,
        'timeout'             => 30.0,
        'redirects'           => false,
        'max_redirects'       => 10,
        'strict_redirects'    => true,
        'redirects_schemes'   => ['http', 'https'],
        'referer_header'      => true,
        'redirects_history'   => true,
        'receive_body'        => true,
        'unchunk_body'        => true,
        'decode_body'         => true,
        'context'             => null,
        'context_opts'        => [],
        'context_params'      => [],
        'debug'               => false,
        'debug_file'          => null,
        'debug_request_body'  => false,
        'debug_response_body' => false

    ];

    /**
     * The client cookie storage.
     *
     * @var \Lazy\Cookie\CookieStorage
     */
    protected $cookieStorage;

    /**
     * The client request origin.
     *
     * @var string
     */
    protected $requestOrigin = '';

    /**
     * The client redirect number.
     *
     * @var int
     */
    protected $redirectNumber = 0;

    /**
     * The client redirects history.
     *
     * @var mixed[]
     */
    protected $redirectsHistory = [];

    /**
     * The client constructor.
     *
     * Allowed client config options:
     *      1.  headers (array, default: empty) - the array of additional request headers.
     *              <code>
     *                  $client = new Client([
     *                      'headers' => [
     *
     *                          'Accept'           => ['text/plain', 'text/html'],
     *                          'X-Requested-With' => 'XMLHttpRequest'
     *
     *                      ]
     *                  ]);
     *              </code>
     *      2.  cookies (bool, default: true) - enable cookies.
     *      3.  cookies_file (string, default: null) - filename to store the cookies.
     *      4.  proxy (array, default: empty) - the array of HTTP proxy URI.
     *              <code>
     *                  $client = new Client([
     *                      'proxy' => [
     *
     *                          'http'  => '1.1.1.1:8080',
     *                          'https' => '2.2.2.2:8080'
     *
     *                      ]
     *                  ]);
     *
     *                  // You can pass the HTTP proxy authorization credentials in the URI:
     *                  // user:password@1.1.1.1:8080
     *              </code>
     *      5.  basic_auth (array, default: empty) - the array of Basic HTTP authorization credentials.
     *              <code>
     *                  $client = new Client([
     *                      'basic_auth' => [
     *
     *                          'user'     => 'user_name',
     *                          'password' => 'user_password'
     *
     *                      ]
     *                  ]);
     *              </code>
     *      6.  origin_header (bool, default: false) - add an "Origin" header to requests.
     *      7.  timeout (float, default: 30.0) - client timeout.
     *      8.  redirects (bool, default: false) - enable redirect requests.
     *      9.  max_redirects (int, default: 10) - redirect requests limit.
     *      10. strict_redirects (bool, default: true) - perform an "RFC 7230" compliant redirect requests
     *              (POST redirect requests are sent as POST requests instead of GET requests).
     *      11. redirects_schemes (array, default: ['http', 'https']) - the array of schemes allowed for redirect requests.
     *      12. referer_header (bool, default: true) - add a "Referer" header to redirect requests.
     *      13. redirects_history (bool, default: true) - store redirect requests URI and headers.
     *              <code>
     *                  // Get the redirect request host and headers:
     *                  $host = $client->getRedirectsHistory()[0]['uri']->getHost();
     *                  $headers = $client->getRedirectsHistory()[0]['headers'];
     *              </code>
     *      14. receive_body (bool, default: true) - receive a response body.
     *      15. unchunk_body (bool, default: true) - unchunk a response body with a "Transfer-Encoding: chunked" header.
     *      16. decode_body (bool, default: true) - decode a response body with a "Content-Encoding" header
     *              (allowed encoding formats: gzip, deflate, compress).
     *      17. context (resource, default: null) - stream socket context.
     *      18. context_opts (array, default: empty) - the array of stream socket context options.
     *      19. context_params (array, default: empty) - the array of stream socket context parameters.
     *      20. debug (bool, default: false) - enable debug output.
     *      21. debug_file (string, default: null) - filename to write the debug output.
     *      22. debug_request_body (bool, default: false) - write a request body to the debug output.
     *      23. debug_response_body (bool, default: false) - write a response body to the debug output.
     *
     * @param mixed[] $config
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
        $this->cookieStorage = new CookieStorage;

        if ($this->config['cookies'] && null !== $this->config['cookies_file']) {
            $this->cookieStorage->loadCookies($this->config['cookies_file']);
            $this->cookieStorage->clearExpiredCookies();
        }
    }

    /**
     * The client destructor.
     */
    public function __destruct()
    {
        try {
            if ($this->config['cookies'] && null !== $this->config['cookies_file']) {
                $this->cookieStorage->clearSessionCookies();
                $this->cookieStorage->clearExpiredCookies();
                $this->cookieStorage->storeCookies($this->config['cookies_file']);
            }
        } catch (Throwable $e) {
            trigger_error($e->getMessage(), \E_USER_ERROR);
        }
    }

    /**
     * Get the client cookie storage.
     *
     * @return \Lazy\Cookie\CookieStorage
     */
    public function getCookieStorage(): CookieStorage
    {
        return $this->cookieStorage;
    }

    /**
     * Get the client redirects history.
     *
     * @return mixed[]
     */
    public function getRedirectsHistory(): array
    {
        return $this->redirectsHistory;
    }

    /**
     * Clear the client redirects history.
     *
     * @return void
     */
    public function clearRedirectsHistory(): void
    {
        $this->redirectsHistory = [];
    }

    /**
     * Set the array of client config.
     *
     * @param  mixed[]  $config
     * @return void
     */
    public function setConfig(array $config): void
    {
        if (isset($config['headers']) && is_array($config['headers'])) {
            $this->config['headers'] = $config['headers'];
        }

        if (isset($config['cookies']) && is_bool($config['cookies'])) {
            $this->config['cookies'] = $config['cookies'];
        }

        if (isset($config['cookies_file']) && is_string($config['cookies_file'])) {
            $this->config['cookies_file'] = $config['cookies_file'];
        }

        if (isset($config['proxy']) && is_array($config['proxy'])) {
            $this->config['proxy'] = $config['proxy'];
        }

        if (isset($config['basic_auth']) && is_array($config['basic_auth'])) {
            $this->config['basic_auth'] = $config['basic_auth'];
        }

        if (isset($config['origin_header']) && is_bool($config['origin_header'])) {
            $this->config['origin_header'] = $config['origin_header'];
        }

        if (isset($config['timeout']) && is_float($config['timeout'])) {
            $this->config['timeout'] = $config['timeout'];
        }

        if (isset($config['redirects']) && is_bool($config['redirects'])) {
            $this->config['redirects'] = $config['redirects'];
        }

        if (isset($config['max_redirects']) && is_int($config['max_redirects'])) {
            $this->config['max_redirects'] = $config['max_redirects'];
        }

        if (isset($config['strict_redirects']) && is_bool($config['strict_redirects'])) {
            $this->config['strict_redirects'] = $config['strict_redirects'];
        }

        if (isset($config['redirects_schemes']) && is_array($config['redirects_schemes'])) {
            $this->config['redirects_schemes'] = $config['redirects_schemes'];
        }

        if (isset($config['referer_header']) && is_bool($config['referer_header'])) {
            $this->config['referer_header'] = $config['referer_header'];
        }

        if (isset($config['redirects_history']) && is_bool($config['redirects_history'])) {
            $this->config['redirects_history'] = $config['redirects_history'];
        }

        if (isset($config['receive_body']) && is_bool($config['receive_body'])) {
            $this->config['receive_body'] = $config['receive_body'];
        }

        if (isset($config['unchunk_body']) && is_bool($config['unchunk_body'])) {
            $this->config['unchunk_body'] = $config['unchunk_body'];
        }

        if (isset($config['decode_body']) && is_bool($config['decode_body'])) {
            $this->config['decode_body'] = $config['decode_body'];
        }

        if (isset($config['context']) && is_resource($config['context'])) {
            $this->config['context'] = $config['context'];
        }

        if (isset($config['context_opts']) && is_array($config['context_opts'])) {
            $this->config['context_opts'] = $config['context_opts'];
        }

        if (isset($config['context_params']) && is_array($config['context_params'])) {
            $this->config['context_params'] = $config['context_params'];
        }

        if (isset($config['debug']) && is_bool($config['debug'])) {
            $this->config['debug'] = $config['debug'];
        }

        if (isset($config['debug_file']) && is_string($config['debug_file'])) {
            $this->config['debug_file'] = $config['debug_file'];
        }

        if (isset($config['debug_request_body']) && is_bool($config['debug_request_body'])) {
            $this->config['debug_request_body'] = $config['debug_request_body'];
        }

        if (isset($config['debug_response_body']) && is_bool($config['debug_response_body'])) {
            $this->config['debug_response_body'] = $config['debug_response_body'];
        }
    }

    /**
     * Make a GET HTTP request.
     *
     * @param  \Psr\Http\Message\UriInterface|string  $uri
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function get($uri, array $headers = [], string $protocolVersion = '1.1'): ResponseInterface
    {
        return $this->request(Method::GET, $uri, $headers, $protocolVersion);
    }

    /**
     * Make a PUT HTTP request.
     *
     * @param  \Psr\Http\Message\UriInterface|string  $uri
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function put($uri, array $headers = [], string $protocolVersion = '1.1'): ResponseInterface
    {
        return $this->request(Method::PUT, $uri, $headers, $protocolVersion);
    }

    /**
     * Make a HEAD HTTP request.
     *
     * @param  \Psr\Http\Message\UriInterface|string  $uri
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function head($uri, array $headers = [], string $protocolVersion = '1.1'): ResponseInterface
    {
        return $this->request(Method::HEAD, $uri, $headers, $protocolVersion);
    }

    /**
     * Make a POST HTTP request.
     *
     * @param  \Psr\Http\Message\UriInterface|string  $uri
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function post($uri, array $headers = [], string $protocolVersion = '1.1'): ResponseInterface
    {
        return $this->request(Method::POST, $uri, $headers, $protocolVersion);
    }

    /**
     * Make a PATCH HTTP request.
     *
     * @param  \Psr\Http\Message\UriInterface|string  $uri
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function patch($uri, array $headers = [], string $protocolVersion = '1.1'): ResponseInterface
    {
        return $this->request(Method::PATCH, $uri, $headers, $protocolVersion);
    }

    /**
     * Make a TRACE HTTP request.
     *
     * @param  \Psr\Http\Message\UriInterface|string  $uri
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function trace($uri, array $headers = [], string $protocolVersion = '1.1'): ResponseInterface
    {
        return $this->request(Method::TRACE, $uri, $headers, $protocolVersion);
    }

    /**
     * Make a DELETE HTTP request.
     *
     * @param  \Psr\Http\Message\UriInterface|string  $uri
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function delete($uri, array $headers = [], string $protocolVersion = '1.1'): ResponseInterface
    {
        return $this->request(Method::DELETE, $uri, $headers, $protocolVersion);
    }

    /**
     * Make an OPTIONS HTTP request.
     *
     * @param  \Psr\Http\Message\UriInterface|string  $uri
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function options($uri, array $headers = [], string $protocolVersion = '1.1'): ResponseInterface
    {
        return $this->request(Method::OPTIONS, $uri, $headers, $protocolVersion);
    }

    /**
     * Make a CONNECT HTTP request.
     *
     * @param  \Psr\Http\Message\UriInterface|string  $uri
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function connect($uri, array $headers = [], string $protocolVersion = '1.1'): ResponseInterface
    {
        return $this->request(Method::CONNECT, $uri, $headers, $protocolVersion);
    }

    /**
     * Make an HTTP request.
     *
     * @param  string  $method
     * @param  \Psr\Http\Message\UriInterface|string  $uri
     * @param  mixed[]  $headers
     * @param  string  $protocolVersion
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function request(string $method, $uri, array $headers = [], string $protocolVersion = '1.1'): ResponseInterface
    {
        return $this->sendRequest(new Request($method, $uri, $headers, $protocolVersion));
    }
}
