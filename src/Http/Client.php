<?php

namespace Lazy\Http;

use ArrayAccess;
use Psr\Http\Client\ClientInterface;

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

        'headers'             => [],
        'cookies'             => true,
        'cookies_file'        => null,
        'proxy'               => false,
        'http_proxy'          => null,
        'https_proxy'         => null,
        'basic_auth'          => false,
        'basic_auth_user'     => null,
        'basic_auth_password' => null,
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
        $this->config = array_merge(
            static::DEFAULT_CONFIG_OPTIONS, $this->prepareConfig($config)
        );
    }

    /**
     * Prepare a client config options.
     *
     * @param  array  $config
     * @return array
     */
    protected function prepareConfig(array $config)
    {
        return $config;
    }
}
