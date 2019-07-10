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

        'headers' => []

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
        $this->config = array_merge(static::DEFAULT_CONFIG_OPTIONS, $config);
    }
}
