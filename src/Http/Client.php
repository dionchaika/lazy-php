<?php

namespace Lazy\Http;

use ArrayAccess;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The PSR-18 HTTP client implementation class.
 *
 * @see https://www.php-fig.org/psr/psr-18/
 */
class Client implements ArrayAccess, ClientInterface
{
    /**
     * The default config options.
     */
    const DEFAULT_CONFIG_OPTIONS = [

        'headers' => [],
        'redirect' => [

            'max'     => 10,
            'strict'  => true,
            'referer' => true,
            'history' => true,
            'schemes' => ['https', 'https']

        ]

    ];

    /**
     * The array of client config options.
     *
     * @var array
     */
    protected $config = [];

    /**
     * The client constructor.
     *
     * @param  array  $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $this->setConfig($config);
    }

    /**
     * Set an array of client config options.
     *
     * @param  array  $config
     * @return void
     */
    public function setConfig(array $config)
    {
        $this->config = array_merge(static::DEFAULT_CONFIG_OPTIONS, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        //
    }

    /**
     * Make an HTTP request.
     *
     * @param  string  $method
     * @param  \Psr\Http\Message\UriInterface|string  $uri
     * @param  array  $opts
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function request($method = Method::GET, $uri = '/', $opts = []): ResponseInterface
    {
        $request = new Request($method, $uri);

        if (isset($opts['data'])) {
            $request = $request->withBody(
                create_stream($opts['data'])
            );
        } else if (isset($opts['xml'])) {
            $request = $request->withXml($opts['xml']);
        } else if (isset($opts['json'])) {
            $request = $request->withJson($opts['json']);
        } else if (isset($opts['form_data'])) {
            $request = $request->withFormData($opts['form_data']);
        } else if (isset($opts['urlencoded'])) {
            $request = $request->withUrlencoded($opts['urlencoded']);
        }

        return $this->sendRequest($request);
    }
}
