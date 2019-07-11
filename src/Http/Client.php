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
     * Get the client config option.
     *
     * @param  string|null  $name
     * @return mixed|null
     */
    public function getConfig($name = null)
    {
        if (! $name) {
            return $this->config;
        }

        return isset($this->config[$name]) ? $this->config[$name] : null;
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
     * Get the client config option.
     *
     * @param  string  $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->getConfig($name);
    }

    /**
     * Set the client config option.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setConfig([$name => $value]);
    }

    /**
     * Check is the client config option set.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    /**
     * Get the client config option.
     *
     * @param  string  $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->getConfig($offset);
    }

    /**
     * Set the client config option.
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setConfig([$offset => $value]);
    }

    /**
     * Unset the client config option.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }
}
